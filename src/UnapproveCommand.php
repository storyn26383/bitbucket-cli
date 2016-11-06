<?php

namespace Sasaya\Bitbucket;

class UnapproveCommand extends BaseApproveCommand
{
    protected $name = 'unapprove';
    protected $description = 'Unapprove a commit.';
    protected $httpMethod = 'DELETE';
}
