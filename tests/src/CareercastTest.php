<?php namespace JobBrander\Jobs\Client\Providers\Test;

use JobBrander\Jobs\Client\Providers\Careercast;
use Mockery as m;

class CareercastTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new Careercast();
    }

    public function testItAll()
    {
        $this->client->setKeyword('engineer')
            ->setCity('Chicago')
            ->setState('IL')
            ->getJobs();
    }

}
