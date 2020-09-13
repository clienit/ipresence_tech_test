<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QuoteControllerTest extends WebTestCase
{
    public function testShoutSuccess()
    {
        $client = static::createClient();       
        
        $client->request('GET', '/shout/steve-jobs?limit=3');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testShoutMaxLimitExceed()
    {
        $client = static::createClient();      

        $client->request('GET', '/shout/steve-jobs?limit=15');
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testShoutMaxLimitNotExceed()
    {
        $client = static::createClient();

        $client->request('GET', '/shout/steve-jobs?limit=1');
        $this->assertCount(1, json_decode ($client->getResponse()->getContent()));
    }

    public function testShoutAddExclamationMark()
    {
        $client = static::createClient();

        $client->request('GET', '/shout/steve-jobs?limit=1');
        $quote = json_decode($client->getResponse()->getContent());
        $this->assertEquals('!', substr($quote[0], -1));
    }

}