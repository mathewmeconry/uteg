<?php

namespace uteg\ACL;

use Symfony\Component\Security\Acl\Permission\MaskBuilder as BaseMaskBuilder;


class MaskBuilder extends BaseMaskBuilder {
    const MASK_DASHBOARD = 256; // 1 << 8
    const MASK_STARTERS_VIEW = 512; // 1 << 9
    const MASK_STARTERS_EDIT = 1024; // 1 << 10
    const MASK_CLUBS_VIEW = 2048; // 1 << 11
    const MASK_CLUBS_EDIT = 4096; // 1 << 12
    const MASK_SETTINGS_VIEW = 8192; // 1 << 13
    const MASK_SETTINGS_EDIT = 16384; // 1 << 14
    const MASK_PERMISSIONS_VIEW = 32768; // 1 << 15
    const MASK_PERMISSIONS_EDIT = 65536; // 1 << 16
    const MASK_OWNER = 131072; // 1 << 17
    const MASK_MASTER = 262144; // 1 << 18

    const CODE_DASHBOARD = 'D';
    const CODE_STARTERS_VIEW = 'STV';
    const CODE_STARTERS_EDIT = 'STE';
    const CODE_CLUBS_VIEW = 'CV';
    const CODE_CLUBS_EDIT = 'CE';
    const CODE_SETTINGS_VIEW = 'SEV';
    const CODE_SETTINGS_EDIT = 'SEE';
    const CODE_PERMISSIONS_VIEW = 'PEV';
    const CODE_PERMISSIONS_EDIT = 'PEE';
}