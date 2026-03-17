# HIPAA Implementation Plan

This document maps HIPAA requirements to concrete architecture and resources for the patient portal. Use it for planning and when adding features that touch PHI.

---

## 1. Data Encryption (At Rest and In Transit)

| Requirement | Implementation |
|-------------|----------------|
| **At rest** | Use Laravel’s encryption for sensitive fields where appropriate; ensure DB and file storage run on infrastructure that supports encryption (e.g. encrypted volumes). For full-disk encryption, rely on HIPAA-compliant hosting (BAA). Consider field-level encryption for high-sensitivity PHI columns. |
| **In transit** | Enforce HTTPS only (TLS 1.2+). Use `APP_URL=https://...`, secure cookies, and HSTS in production. No PHI over unencrypted channels. |

**Resources to build/verify**

- [x] Config: force HTTPS in production (`URL::forceScheme('https')` in AppServiceProvider). Session cookie `secure` defaults to true in production (`config/session.php`). Set `APP_URL=https://...` and `SESSION_LIFETIME` (minutes; HIPAA: consider ≤120) in production.
- [ ] Identify all tables/columns that store PHI; document and, if needed, add field-level encryption or ensure infrastructure encryption.
- [ ] Hosting: confirm BAA and encrypted storage with provider.

---

## 2. User Authentication and Access Control

| Requirement | Implementation |
|-------------|----------------|
| **Strong auth** | Fortify already in use. Enforce strong password rules (existing Password rules). MFA: Fortify 2FA is present; ensure it is required for roles that access PHI (or org policy). |
| **Session timeout** | Inactive sessions must expire. Configure `session.lifetime` and consider shorter timeouts for PHI-accessing roles if needed. |
| **RBAC** | Restrict access by role (patient, physician, staff). Use Laravel policies and middleware so only authorized roles can access PHI-related routes and resources. |

**Resources to build/verify**

- [x] **Policies**: PatientPolicy, PhysicianPolicy, OrganizationPolicy enforce view/update/delete by role and relationship.
- [x] **Middleware**: `EnsureUserHasRole` registered as `role`; apply to routes that serve or modify PHI (e.g. `->middleware('role:physician,staff')`).
- [x] **Session**: `SESSION_LIFETIME` (env, minutes) controls idle timeout. Secure cookie in production. Consider ≤120 for PHI access.
- [ ] **MFA**: Decide if MFA is required for physicians/staff; enforce via middleware or Fortify config.

---

## 3. Audit Trails and Activity Logs

| Requirement | Implementation |
|-------------|----------------|
| **Who, when, what** | Log: user id, timestamp, action (view/ create/update/delete), and resource/identifier (e.g. patient id, record id). No PHI in logs; use IDs. |
| **Tamper-resistant** | Store logs in a dedicated table or service, with restricted write access. Prefer append-only or append-only + integrity checks. |
| **Monitoring** | Use logs for alerts (e.g. failed logins, bulk access, after-hours access). Define what “suspicious” means and how to alert. |

**Resources to build**

- [x] **Audit log model/migration**: `audit_logs` table with `user_id`, `action`, `auditable_type`, `auditable_id`, `ip`, `user_agent`, `created_at` (append-only). Indexes on (auditable_type, auditable_id) and (user_id, created_at).
- [x] **Audit service/trait**: `AuditLog::log($action, $type, $id)` and `AuditsPhiAccess` trait with `auditPhi($action, $model)` for controllers. Use in all PHI read/write paths.
- [ ] **Policies/routes**: When adding PHI routes, call `$this->auditPhi('view', $model)` (or create/update/delete) after authorize.
- [x] **Breach support**: Query by user_id, auditable_type/auditable_id, created_at range.

---

## 4. PHI Data Minimization

| Requirement | Implementation |
|-------------|----------------|
| **Minimal collection** | Only collect PHI necessary for the feature. Review forms and APIs; remove optional fields that aren’t needed. |
| **Retention** | Define retention per data type (e.g. visit notes, messages). Implement scheduled jobs or commands to anonymize or delete after retention. |
| **No over-disclosure** | UI and API should expose only the minimum PHI needed for the current role and context. |

**Resources to build/verify**

- [ ] **Data inventory**: List all PHI fields and tables; document purpose and retention.
- [ ] **Retention**: Scheduled task(s) or commands to purge/anonymize per policy; run in a HIPAA-safe way (e.g. encrypted backups before purge if required).
- [ ] **Code review**: New features that add PHI must justify need and retention.

---

## 5. Disaster Recovery and Backups

| Requirement | Implementation |
|-------------|----------------|
| **Backups** | Automated, encrypted backups of DB and any file storage that holds PHI. |
| **Recovery** | Document and periodically test restore procedure. |
| **Security** | Backup storage with access controls and encryption; BAA if outsourced. |

**Resources to build/verify**

- [ ] Backup strategy (DB + files) with encryption and access control.
- [ ] Documented restore runbook and test schedule.
- [ ] Hosting/backup provider BAA and compliance.

---

## 6. Secure Hosting and BAAs

| Requirement | Implementation |
|-------------|----------------|
| **BAA** | All vendors that store/process PHI (hosting, DB, backup, email/SMS if used for PHI) must have a signed BAA. |
| **Infrastructure** | Use HIPAA-eligible services (e.g. AWS/Azure/GCP with BAA); harden config (encryption, networking, logging). |

**Resources to verify**

- [ ] List all third-party services that touch PHI; confirm BAA and HIPAA-eligible config.
- [ ] No PHI in SMS: use SMS only for non-PHI (e.g. appointment reminders without details); use in-app, encrypted messaging for PHI.

---

## 7. Common Pitfalls to Avoid

- **Over-collecting PHI**: Every new field that is PHI must be justified and documented.
- **Unencrypted PHI**: No storing or transmitting PHI in plain text.
- **Missing audit trails**: Any access or change to PHI must be logged.
- **Non-compliant vendors**: No PHI with vendors without BAA and HIPAA-eligible setup.
- **Unclear policies**: Privacy policy and terms must be clear and accessible; code should support consent and policy links where applicable.

---

## 8. Implementation Priority (Suggested)

1. **RBAC + policies** – Lock down who can see what (patient/physician/org staff).
2. **Audit logging** – Implement audit table and logging for all PHI access/changes.
3. **Session and auth** – Session timeout, MFA policy, HTTPS enforcement.
4. **PHI inventory and minimization** – Document PHI, retention, and purge/anonymize.
5. **Encryption and hosting** – Confirm at-rest and in-transit encryption and BAA with providers.
6. **Backup and DR** – Automated encrypted backups and tested recovery.
7. **Ongoing** – Risk assessments, dependency and config reviews, and updates to this plan as the app grows.

---

## Reference

- Source: [HIPAA Compliant Software Development (Aloa)](https://aloa.co/blog/hipaa-compliant-software-development)
- Internal rule: `.cursor/rules/hipaa-compliance.mdc` (always-on for this project)
