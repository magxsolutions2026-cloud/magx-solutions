# Appointment Booking Setup

1. Run schema in Supabase SQL editor:
- File: `docs/appointments_schema.sql`

2. Configure environment variables (Vercel project settings):
- Database: `DATABASE_URL` (or `SUPABASE_DB_*` values)
- Supabase Auth: `SUPABASE_URL`, `SUPABASE_ANON_KEY`
- Booking config: `APPOINTMENT_TIMEZONE`, `APPOINTMENT_BUSINESS_HOUR_START`, `APPOINTMENT_BUSINESS_HOUR_END`, `APPOINTMENT_SLOT_MINUTES`, `APPOINTMENT_SLOT_CAPACITY`
- Approval defaults: `DEFAULT_ZOOM_MEETING_LINK`, `APPOINTMENTS_ADMIN_EMAILS`
- SMTP: `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASSWORD`, `SMTP_FROM_EMAIL`, `SMTP_FROM_NAME`, `ADMIN_NOTIFICATION_EMAIL`
- Google Calendar: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REFRESH_TOKEN`, `GOOGLE_CALENDAR_ID`
- Outlook Calendar: `MS_TENANT_ID`, `MS_CLIENT_ID`, `MS_CLIENT_SECRET`, `MS_USER_ID`, `MS_CALENDAR_ID`

3. Supabase admin role requirement:
- `app_metadata.role` (or `roles`) must include `admin` or `super_admin`,
- or include admin email in `APPOINTMENTS_ADMIN_EMAILS`.

4. Flow behavior:
- Public users can only submit pending requests.
- Calendar events and emails are sent only on admin approval.
- Rejected requests stay stored as `rejected`.
