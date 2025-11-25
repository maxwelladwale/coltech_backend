# Email Verification Flow - Frontend Implementation Guide

## Overview

The email verification system redirects users to your frontend application after they click the verification link in their email. This document explains the frontend pages you need to create and the user experience flow.

---

## Backend Behavior

When a user clicks the email verification link, the API will:

1. **Validate the verification link**
2. **Update the user's email_verified_at timestamp** (if valid)
3. **Redirect to your frontend** with appropriate status

---

## Frontend Routes Required

You need to create these pages in your Next.js/React frontend:

### 1. Success Page
**Route:** `/auth/verification/success`

**When triggered:**
- User successfully verifies their email
- User clicks verification link but email is already verified

**URL Parameters:**
- `?already_verified=true` - Email was already verified

**Recommended UI:**
```
✅ Email Verified Successfully!

Your email has been verified. You can now access all features.

[Go to Dashboard] [Continue Shopping]
```

**Implementation Example (Next.js):**
```typescript
// app/auth/verification/success/page.tsx
'use client';

import { useSearchParams } from 'next/navigation';
import Link from 'next/link';

export default function VerificationSuccess() {
  const searchParams = useSearchParams();
  const alreadyVerified = searchParams.get('already_verified') === 'true';

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div className="mb-6">
          <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
            <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>

        <h1 className="text-2xl font-bold text-gray-900 mb-2">
          {alreadyVerified ? 'Email Already Verified' : 'Email Verified Successfully!'}
        </h1>

        <p className="text-gray-600 mb-6">
          {alreadyVerified
            ? 'Your email was already verified. You can continue using all features.'
            : 'Your email has been verified. You can now access all features of COLTECH.'}
        </p>

        <div className="space-y-3">
          <Link
            href="/dashboard"
            className="block w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition"
          >
            Go to Dashboard
          </Link>
          <Link
            href="/products"
            className="block w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-200 transition"
          >
            Continue Shopping
          </Link>
        </div>
      </div>
    </div>
  );
}
```

---

### 2. Failed Page
**Route:** `/auth/verification/failed`

**When triggered:**
- Invalid verification link (tampered or malformed)
- Expired verification link

**URL Parameters:**
- `?reason=invalid` - Link is invalid or tampered
- `?reason=expired` - Link has expired (if implemented)

**Recommended UI:**
```
❌ Verification Failed

The verification link is invalid or has expired.

[Request New Verification Email] [Contact Support]
```

**Implementation Example (Next.js):**
```typescript
// app/auth/verification/failed/page.tsx
'use client';

import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import { useState } from 'react';

export default function VerificationFailed() {
  const searchParams = useSearchParams();
  const reason = searchParams.get('reason');
  const [resending, setResending] = useState(false);
  const [resent, setResent] = useState(false);

  const handleResendVerification = async () => {
    setResending(true);
    try {
      const token = localStorage.getItem('auth_token'); // Or your auth storage
      const response = await fetch('http://localhost:8000/api/auth/email/resend', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });

      if (response.ok) {
        setResent(true);
      } else {
        alert('Failed to resend verification email. Please try again.');
      }
    } catch (error) {
      alert('An error occurred. Please try again.');
    } finally {
      setResending(false);
    }
  };

  const getMessage = () => {
    if (reason === 'invalid') {
      return 'The verification link is invalid or has been tampered with.';
    }
    if (reason === 'expired') {
      return 'The verification link has expired. Please request a new one.';
    }
    return 'The verification link is no longer valid.';
  };

  if (resent) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
          <div className="mb-6">
            <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto">
              <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
          </div>

          <h1 className="text-2xl font-bold text-gray-900 mb-2">
            Verification Email Sent!
          </h1>

          <p className="text-gray-600 mb-6">
            We've sent a new verification email to your inbox. Please check your email and click the verification link.
          </p>

          <Link
            href="/"
            className="block w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-200 transition"
          >
            Return to Home
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div className="mb-6">
          <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto">
            <svg className="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
        </div>

        <h1 className="text-2xl font-bold text-gray-900 mb-2">
          Verification Failed
        </h1>

        <p className="text-gray-600 mb-6">
          {getMessage()}
        </p>

        <div className="space-y-3">
          <button
            onClick={handleResendVerification}
            disabled={resending}
            className="block w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition disabled:bg-gray-400"
          >
            {resending ? 'Sending...' : 'Request New Verification Email'}
          </button>
          <Link
            href="/contact"
            className="block w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-200 transition"
          >
            Contact Support
          </Link>
        </div>
      </div>
    </div>
  );
}
```

---

## Complete User Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│ 1. User registers on website                                │
│    POST /api/auth/signup                                     │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. Backend sends two emails:                                │
│    - Welcome email (introduction to COLTECH)                │
│    - Verification email (with verification link)            │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. User receives email and clicks verification link         │
│    GET /api/auth/email/verify/{id}/{hash}                   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. Backend validates link:                                  │
│    ✓ Check if hash matches user email                       │
│    ✓ Check if link has expired (60 minutes)                │
│    ✓ Check if already verified                              │
└────────────────────┬────────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        ▼                         ▼
┌─────────────────┐      ┌─────────────────┐
│ Valid Link      │      │ Invalid Link    │
└────────┬────────┘      └────────┬────────┘
         │                        │
         ▼                        ▼
┌─────────────────┐      ┌─────────────────┐
│ Mark email as   │      │ Redirect to     │
│ verified        │      │ /verification/  │
│                 │      │ failed          │
└────────┬────────┘      └─────────────────┘
         │
         ▼
┌─────────────────┐
│ Redirect to     │
│ /verification/  │
│ success         │
└─────────────────┘
```

---

## Environment Configuration

Add to your `.env` file:

```env
# Backend API
FRONTEND_URL=http://localhost:3000

# For production
# FRONTEND_URL=https://coltech.co.ke
```

---

## API Endpoints Summary

| Endpoint | Method | Auth | Purpose |
|----------|--------|------|---------|
| `/api/auth/signup` | POST | No | Register + send emails |
| `/api/auth/email/verify/{id}/{hash}` | GET | No | Verify email (redirects to frontend) |
| `/api/auth/email/resend` | POST | Yes | Resend verification email |

---

## Redirect URLs

The backend will redirect to these frontend URLs:

### Success Cases:
- **New verification:** `{FRONTEND_URL}/auth/verification/success`
- **Already verified:** `{FRONTEND_URL}/auth/verification/success?already_verified=true`

### Error Cases:
- **Invalid link:** `{FRONTEND_URL}/auth/verification/failed?reason=invalid`

---

## Testing Checklist

- [ ] User registers and receives both emails
- [ ] User clicks verification link and sees success page
- [ ] User clicks same link again and sees "already verified" message
- [ ] Invalid/tampered link shows error page
- [ ] Resend verification works from failed page
- [ ] All redirects point to correct frontend URLs
- [ ] Mobile responsive design works
- [ ] Loading states work properly

---

## Production Deployment

### Backend (.env):
```env
FRONTEND_URL=https://coltech.co.ke
```

### Frontend:
- Create the two verification pages
- Update API base URL for production
- Test all redirect scenarios
- Set up monitoring for failed verifications

---

## Additional Features to Consider

1. **Auto-login after verification**
   - Generate new token after verification
   - Pass token in redirect URL (secure method needed)

2. **Email preferences**
   - Allow users to update email
   - Re-verification required for new email

3. **Verification reminders**
   - Send reminder if not verified after 24 hours
   - Show banner in app for unverified users

4. **Analytics**
   - Track verification success rate
   - Monitor link expiration issues
   - Measure time to verification

---

## Support

For questions or issues, contact the development team or check the API documentation.
