<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Role extends Enum
{
    const ADMIN = 1;
    const CREATOR = 2;
    const FAN = 3;
    const AGENCY = 4;
    const AFFILIATER = 5;
    const CS = 6;
}
