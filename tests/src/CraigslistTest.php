<?php namespace JobBrander\Jobs\Client\Providers\Test;

use JobBrander\Jobs\Client\Providers\Craigslist;
use Mockery as m;

class CraigslistTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new Craigslist();
    }

    public function testItAll()
    {
        $this->client->setKeyword('engineer')
            ->setCity('Chicago')
            ->setState('IL')
            ->getJobs();
    }

}
