<?php

return [
    // General
    'success' => 'Success',
    'error' => 'Error',
    'created' => 'Created successfully',
    'not_found' => 'Resource not found',
    'forbidden' => 'Forbidden',
    'unauthorized' => 'Unauthorized',
    'validation_failed' => 'Validation failed',
    'invalid_role' => 'Invalid role',

    // Auth
    'auth' => [
        'login_success' => 'Login successful',
        'invalid_credentials' => 'Invalid credentials',
        'unauthorized' => 'Unauthorized',
        'logged_out' => 'Logged out successfully',
        'password_reset_sent' => 'If your email exists, you will receive a password reset link.',
        'password_reset_success' => 'Password reset successful. Please login with your new password.',
        'invalid_token' => 'Invalid token or email',
    ],

    // Users
    'users' => [
        'retrieved' => 'Users retrieved successfully',
        'created' => 'User created successfully',
        'not_found' => 'User not found',
        'updated' => 'User updated successfully',
        'deleted' => 'User deleted successfully',
        'restored' => 'User restored successfully',
        'cannot_change_super_admin' => 'Cannot change Super Admin role',
        'cannot_delete_super_admin' => 'Cannot delete Super Admin',
        'cannot_create_admin' => 'Admin cannot create other Admin accounts',
        'cannot_manage_admin' => 'Admin cannot manage other Admin accounts',
        'failed_retrieve' => 'Failed to retrieve users: :message',
        'failed_create' => 'Failed to create user: :message',
        'failed_update' => 'Failed to update user: :message',
        'failed_delete' => 'Failed to delete user: :message',
        'failed_restore' => 'Failed to restore user: :message',
    ],

    // Merchants
    'merchants' => [
        'retrieved' => 'Merchants retrieved successfully',
        'created' => 'Merchant created successfully',
        'not_found' => 'Merchant not found',
        'updated' => 'Merchant updated successfully',
        'deleted' => 'Merchant deleted successfully',
        'already_exists' => 'User already has a merchant profile',
        'forbidden_view' => 'Forbidden. You can only view your own merchant profile.',
        'forbidden_update' => 'Forbidden. You can only update your own merchant profile.',
        'forbidden_delete' => 'Forbidden. Merchants cannot delete merchant profiles.',
        'failed_retrieve' => 'Failed to retrieve merchants: :message',
        'failed_create' => 'Failed to create merchant: :message',
        'failed_update' => 'Failed to update merchant: :message',
        'failed_delete' => 'Failed to delete merchant: :message',
    ],

    // Dashboard
    'dashboard' => [
        'retrieved' => 'Dashboard data retrieved successfully',
        'super_admin' => 'Super Admin Dashboard data',
        'admin' => 'Admin Dashboard data',
        'merchant' => 'Merchant Dashboard data',
        'merchant_not_found' => 'Merchant profile not found. Please contact admin.',
    ],

    // Settings
    'settings' => [
        'profile_retrieved' => 'Profile retrieved successfully',
        'profile_updated' => 'Profile updated successfully',
        'password_changed' => 'Password changed successfully',
        'current_password_wrong' => 'Current password is incorrect',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Name is required',
        'name_max' => 'Name must be less than 255 characters',
        'email_required' => 'Email is required',
        'email_email' => 'Invalid email format',
        'email_unique' => 'Email already used',
        'email_regex' => 'Invalid email format',
        'email_max' => 'Email cannot exceed 255 characters',
        'password_required' => 'Password is required',
        'password_min' => 'Password must be at least 6 characters',
        'password_max' => 'Password cannot exceed 255 characters',
        'password_confirmed' => 'Password confirmation does not match',
        'role_required' => 'Role is required',
        'role_in' => 'Invalid role',
        'user_id_required' => 'User ID is required',
        'user_id_exists' => 'User not found',
        'business_name_required' => 'Business name is required',
        'business_name_max' => 'Business name must be less than 255 characters',
        'phone_max' => 'Phone must be less than 20 characters',
    ],
];
