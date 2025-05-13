<?php

namespace Tests;

use Tests\Support\AcceptanceTester;

class FormCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/tests/forms/main/index.php');
    }

    public function successfulSubmission(AcceptanceTester $I)
    {
        $I->fillField('name', 'McTester');
        $I->fillField('email', 'McTester@example.com');
        $I->fillField('comments', 'Some very important comments!');
        $I->click(['id' => 'button']);

        $I->see('Thank you, McTester');
    }
}