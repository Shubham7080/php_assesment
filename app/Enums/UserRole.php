<?php

namespace App\Enums;

enum UserRole: string
{
    case Author = 'author';
    case Reviewer = 'reviewer';
    case Admin = 'admin';
}
