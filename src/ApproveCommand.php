<?php

namespace Sasaya\Bitbucket;

class ApproveCommand extends BaseApproveCommand
{
    protected $name = 'approve';
    protected $description = 'Approve a commit.';
    protected $httpMethod = 'POST';
}
