<?php

namespace App\Models\Group;

enum GroupRole : string
{
    case Admin = 'admin';
    case Member = 'member';
}
