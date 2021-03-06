<?php

namespace spec\Team3\PayU\Configuration;

use PhpSpec\ObjectBehavior;
use Team3\PayU\Configuration\Credentials\CredentialsInterface;

class ConfigurationSpec extends ObjectBehavior
{
    public function let(CredentialsInterface $credentials)
    {
        $this
            ->beConstructedWith($credentials);
    }

    public function it_is_initializable()
    {
        $this
            ->shouldHaveType('Team3\PayU\Configuration\Configuration');
    }

    public function it_returns_complete_api_url()
    {
        $this
            ->getAPIUrl()
            ->shouldReturn('https://secure.payu.com/api/v2_1');
    }
}
