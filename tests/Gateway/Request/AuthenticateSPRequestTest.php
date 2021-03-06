<?php
namespace ID3Global\Tests\Gateway\Request;

use ID3Global\Gateway\Request\AuthenticateSPRequest,
    ID3Global\Identity\Address\FixedFormatAddress,
    ID3Global\Identity\Address\FreeFormatAddress,
    ID3Global\Identity\Address\AddressContainer,
    ID3Global\Identity\Identity,
    ID3Global\Identity\Documents\DocumentContainer,
    ID3Global\Identity\Documents\NZ\DrivingLicence;

class AuthenticateSPRequestTest extends \PHPUnit_Framework_TestCase {
    public function testStandardParams() {
        $version = new \stdClass();
        $version->Version = 1;
        $version->ID = 'abc123-x';
        $r = new AuthenticateSPRequest();

        $r->setCustomerReference('X')->setProfileIDVersion($version);

        $this->assertEquals(1, $r->getProfileIDVersion()->Version);
        $this->assertEquals('abc123-x', $r->getProfileIDVersion()->ID);
        $this->assertEquals('X', $r->getCustomerReference());
    }

    public function testFixedLengthAddress() {
        $identity = new Identity();
        $container = new AddressContainer();
        $address = new FixedFormatAddress();
        $address
            ->setPrincipality('US')
            ->setCountry('US')
            ->setStateDistrict('NY')
            ->setRegion('New York')
            ->setCity('New York')
            ->setSubCity('Manhattan')
            ->setStreet('5th Ave')
            ->setSubStreet('5th Ave')
            ->setBuilding('350')
            ->setSubBuilding('350')
            ->setPremise('Empire State Building')
            ->setZipPostcode('10118');

        $container->setCurrentAddress($address);
        $identity->setAddresses($container);

        $r = new AuthenticateSPRequest();
        $r->addFieldsFromIdentity($identity);
        $test = $r->getInputData()->Addresses->CurrentAddress;

        $this->assertSame('US', $test->Principality);
        $this->assertSame('US', $test->Country);
        $this->assertSame('NY', $test->StateDistrict);
        $this->assertSame('New York', $test->Region);
        $this->assertSame('New York', $test->City);
        $this->assertSame('Manhattan', $test->SubCity);
        $this->assertSame('5th Ave', $test->Street);
        $this->assertSame('5th Ave', $test->SubStreet);
        $this->assertSame('350', $test->Building);
        $this->assertSame('350', $test->SubBuilding);
        $this->assertSame('Empire State Building', $test->Premise);
        $this->assertSame('10118', $test->ZipPostcode);
    }

    public function testFreeFormatAddress() {
        $identity = new Identity();
        $container = new AddressContainer();
        $address = new FreeFormatAddress();

        $address
            ->setCountry('New Zealand')
            ->setPostCode('6004')
            ->setAddressLine1('Room 6')
            ->setAddressLine2('Level 6')
            ->setAddressLine3('Area 6')
            ->setAddressLine4('666 Fake St')
            ->setAddressLine5('Te Aro')
            ->setAddressLine6('Wellington')
            ->setAddressLine7('6004')
            ->setAddressLine8('NZ');

        $container->setCurrentAddress($address);
        $identity->setAddresses($container);

        $r = new AuthenticateSPRequest();
        $r->addFieldsFromIdentity($identity);
        $test = $r->getInputData()->Addresses->CurrentAddress;

        $this->assertSame('New Zealand', $test->Country);
        $this->assertSame('6004', $test->PostCode);
        $this->assertSame('Room 6', $test->AddressLine1);
        $this->assertSame('Level 6', $test->AddressLine2);
        $this->assertSame('Area 6', $test->AddressLine3);
        $this->assertSame('666 Fake St', $test->AddressLine4);
        $this->assertSame('Te Aro', $test->AddressLine5);
        $this->assertSame('Wellington', $test->AddressLine6);
        $this->assertSame('6004', $test->AddressLine7);
        $this->assertSame('NZ', $test->AddressLine8);
    }

    public function testNZDrivingLicence() {
        $identity = new Identity();
        $container = new DocumentContainer();
        $licence = new DrivingLicence();

        $licence
            ->setNumber('DI123456')
            ->setVersion(123)
            ->setVehicleRegistration('ABC123');

        $container->addIdentityDocument($licence, 'New Zealand');

        $identity->setIdentityDocuments($container);

        $r = new AuthenticateSPRequest();
        $r->addFieldsFromIdentity($identity);
        $test = $r->getInputData()->IdentityDocuments;

        $this->assertSame('DI123456', $test->NewZealand->DrivingLicence->Number);
        $this->assertSame(123, $test->NewZealand->DrivingLicence->Version);
        $this->assertSame('ABC123', $test->NewZealand->DrivingLicence->VehicleRegistration);
    }
}
