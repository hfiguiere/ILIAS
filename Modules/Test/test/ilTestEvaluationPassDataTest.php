<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestEvaluationPassDataTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestEvaluationPassDataTest extends ilTestBaseTestCase
{
    private ilTestEvaluationPassData $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestEvaluationPassData();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestEvaluationPassData::class, $this->testObj);
    }
}