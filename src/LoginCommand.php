<?php

namespace Sasaya\Bitbucket;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoginCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('login')
             ->setDescription('Login with your Bitbucket credentials.');
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
        // TODO: refactor this code
        $helper = $this->getHelper('question');

        $question = new Question('Enter your username: ');
        $username = $helper->ask($input, $output, $question);

        $question = new Question('Enter your password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $question);

        $client = new Client();

        try {
            $response = $client->request(
                'POST',
                'https://bitbucket.org/site/oauth2/access_token',
                [
                    'auth' => [
                        BITBUCKET_KEY,
                        BITBUCKET_SECRET,
                    ],
                    'form_params' => [
                        'grant_type' => 'password',
                        'username' => $username,
                        'password' => $password,
                    ],
                ]
            );
        } catch (ClientException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents());

            $output->writeln("<error>{$response->error_description}</error>");

            return 1;
        }

        $this->setCredentials($response->getBody()->getContents());

        $output->writeln('<info>Success!</info>');

        return 0;
    }
}
