<?php
namespace ID3Global\Tests;

use ID3Global\Stubs\Gateway\GlobalAuthenticationGatewayFake;
use ID3Global\Identity\Identity;
use ID3Global\Identity\PersonalDetails;
use \ID3Global\Service\GlobalAuthenticationService;

class GlobalAuthenticationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GlobalAuthenticationService
     */
    private $service;

    /**
     * @var GlobalAuthenticationGatewayFake
     */
    private $fakeGateway;

    public function setUp() {
        $this->service = new GlobalAuthenticationService();
        $this->fakeGateway = new GlobalAuthenticationGatewayFake('username', 'password');

        $this->service->setGateway($this->fakeGateway);
    }

    public function testSuccessfulResponse() {
        $identity = $this->getSuccessfulIdentity();

        $this->service
            ->setCustomerReference('x')
            ->setIdentity($identity);

        $this->assertSame(Identity::IDENTITY_BAND_PASS, $this->service->verifyIdentity());

        $response = $this->service->getLastVerifyIdentityResponse();
        $this->assertSame(Identity::IDENTITY_BAND_PASS, $response->AuthenticateSPResult->BandText);
        $this->assertSame('Default Profile', $response->AuthenticateSPResult->ProfileName);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage An Identity must be provided by setIdentity() before calling verifyIdentity()
     */
    public function testIdentityIsRequired() {
        $this->service->verifyIdentity();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage An Identity must be provided by setIdentity() before calling verifyIdentity()
     */
    public function testIdentityIsProperlyValidated() {
        $class = new \ReflectionClass('\ID3Global\Service\GlobalAuthenticationService');
        $property = $class->getProperty('identity');
        $property->setAccessible(true);
        $property->setValue($this->service, new \stdClass());

        $this->service->verifyIdentity();
    }

    private function getSuccessfulIdentity() {
        $personalDetails = new PersonalDetails();
        $personalDetails
            ->setForename('Snow')
            ->setMiddleName('White')
            ->setSurname('Huntsman')
            ->setGender('Female')
            ->setDateOfBirth(1976, 3, 6);

        $identity = new Identity();
        $identity->setPersonalDetails($personalDetails);

        return $identity;
    }
}
