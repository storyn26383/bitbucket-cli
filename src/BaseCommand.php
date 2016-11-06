<?php

namespace Sasaya\Bitbucket;

use Symfony\Component\Console\Command\Command;

abstract class BaseCommand extends Command
{
    protected function getCredentialsPath()
    {
        return "{$_SERVER['HOME']}/.bitbucket";
    }

    protected function getCredentials()
    {
        return @json_decode(file_get_contents($this->getCredentialsPath()));
    }

    protected function setCredentials($contents)
    {
        file_put_contents($this->getCredentialsPath(), $contents);

        return $this;
    }
}
