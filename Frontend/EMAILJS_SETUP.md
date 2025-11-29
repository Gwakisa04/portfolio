# EmailJS Setup Guide

This guide will help you configure EmailJS to send contact form messages to your email.

## Step 1: Sign Up for EmailJS

1. Go to https://www.emailjs.com/
2. Click "Sign Up" and create a free account (200 emails/month free)
3. Verify your email address

## Step 2: Add Email Service

1. In EmailJS dashboard, go to "Email Services"
2. Click "Add New Service"
3. Choose "Gmail" (or your preferred email provider)
4. Connect your Gmail account (www44victor@gmail.com)
5. Click "Create Service"
6. **Copy your Service ID** (you'll need this)

## Step 3: Create Email Template

1. Go to "Email Templates" in the dashboard
2. Click "Create New Template"
3. Use this template structure:

**Template Name:** Contact Form

**Subject:** New Contact Form Message - {{subject}}

**Content:**
```
You have received a new message from your portfolio contact form.

From: {{from_name}}
Email: {{from_email}}
Subject: {{subject}}

Message:
{{message}}

---
Reply to: {{reply_to}}
```

4. Click "Save"
5. **Copy your Template ID** (you'll need this)

## Step 4: Get Your Public Key

1. Go to "Account" → "General"
2. Find "Public Key" section
3. **Copy your Public Key**

## Step 5: Update JavaScript Code

1. Open `Frontend/js/script.js`
2. Find these lines (around line 95-98):
```javascript
const EMAILJS_PUBLIC_KEY = 'YOUR_PUBLIC_KEY';
const EMAILJS_SERVICE_ID = 'YOUR_SERVICE_ID';
const EMAILJS_TEMPLATE_ID = 'YOUR_TEMPLATE_ID';
```

3. Replace with your actual values:
```javascript
const EMAILJS_PUBLIC_KEY = 'your-actual-public-key-here';
const EMAILJS_SERVICE_ID = 'your-actual-service-id-here';
const EMAILJS_TEMPLATE_ID = 'your-actual-template-id-here';
```

## Step 6: Test the Form

1. Open your contact page in a browser
2. Fill out the contact form
3. Submit the form
4. Check your email (www44victor@gmail.com) for the message

## Troubleshooting

- **"EmailJS library not loaded"**: Check your internet connection
- **"Service ID not found"**: Make sure you copied the correct Service ID
- **"Template ID not found"**: Make sure you copied the correct Template ID
- **Emails not arriving**: Check your spam folder and verify EmailJS service is connected

## Security Note

The Public Key is safe to use in frontend code. Never share your Private Key.

## Need Help?

Visit EmailJS documentation: https://www.emailjs.com/docs/

