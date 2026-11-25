<?php

namespace App\Enums;

enum CodePurpose: string
{
    case EmailChange = 'email_change';
    case PasswordRecovery = 'password_recovery';
}
