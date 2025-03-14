<p>Hello,</p>
<p>You requested a password reset. Click the link below to reset your password:</p>
<p>
    <a href="{{ $frontendUrl }}/reset-password?token={{ $token }}&email={{ urlencode($email) }}">
                Reset Password
    </a>
</p>
<p>If you did not request this, please ignore this email.</p>
