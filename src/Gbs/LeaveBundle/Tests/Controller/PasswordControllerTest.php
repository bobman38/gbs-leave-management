<?php

namespace Gbs\LeaveBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PasswordControllerTest extends WebTestCase
{
    public function testResetpassword()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/resetPassword');
    }

}
