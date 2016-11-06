<?php

namespace Sasaya\Bitbucket;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseApproveCommand extends BaseCommand
{
    protected $name;
    protected $description;
    protected $httpMethod;

    protected $options = ['username', 'repo', 'commit'];

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description);

        foreach ($this->options as $option) {
            $this->addOption($option, $option[0], InputOption::VALUE_REQUIRED);
        }
    }

    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->options as $option) {
            // TODO: bad code
            if (! ( $$option = $input->getOption($option) )) {
                $output->writeln("<error>Option [{$option}] is required.</error>");

                return 1;
            }
        }


        try {
            $this->request($username, $repo, $commit);
        } catch (ClientException $e) {
            switch ($e->getResponse()->getStatusCode()) {
                case 401:
                    $this->refreshToken();

                    // TODO: bad retry
                    $this->request($username, $repo, $commit);

                    break;

                case 403:
                    $output->writeln('<error>Please login again.</error>');

                    return 1;

                default:
                    $response = json_decode($e->getResponse()->getBody()->getContents());

                    $output->writeln("<error>{$response->error->message}</error>");

                    return 1;
            }
        }

        $output->writeln('<info>Success!</info>');

        return 0;
    }

    protected function request($username, $repo, $commit)
    {
        $credentials = $this->getCredentials();

        if (! $credentials) {
            $output->writeln('<error>Please login first.</error>');

            return 1;
        }

        $client = new Client([
            'base_uri' => 'https://api.bitbucket.org/2.0/',
        ]);

        $response = $client->request(
            $this->httpMethod,
            "repositories/{$username}/{$repo}/commit/{$commit}/approve",
            [
                'headers' => [
                    'Authorization' => "Bearer {$credentials->access_token}",
                ],
            ]
        );

        return $response;
    }

    protected function refreshToken()
    {
        $credentials = $this->getCredentials();

        $client = new Client();
        $response = $client->request(
            'POST',
            'https://bitbucket.org/site/oauth2/access_token',
            [
                'auth' => [
                    BITBUCKET_KEY,
                    BITBUCKET_SECRET,
                ],
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $credentials->refresh_token,
                ],
            ]
        );

        $this->setCredentials($response->getBody()->getContents());
    }
}
