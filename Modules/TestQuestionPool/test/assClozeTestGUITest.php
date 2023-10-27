<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assClozeTestGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setGlobalVariable('ilLog', $this->createMock(ilLogger::class));

        $ilCtrl_mock = $this->getMockBuilder(ilCtrl::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $ilCtrl_mock->method('saveParameter');
        $ilCtrl_mock->method('saveParameterByClass');
        $this->setGlobalVariable('ilCtrl', $ilCtrl_mock);

        $lng_mock = $this->getMockBuilder(ilLanguage::class)
                         ->disableOriginalConstructor()
                         ->onlyMethods(['txt'])
                         ->getMock();
        $lng_mock->method('txt')->will($this->returnValue('Test'));
        $this->setGlobalVariable('lng', $lng_mock);

        $ilias_mock = new stdClass();
        $ilias_mock->account = new stdClass();
        $ilias_mock->account->id = 6;
        $ilias_mock->account->fullname = 'Esther Tester';

        $this->setGlobalVariable('ilias', $ilias_mock);
        $this->setGlobalVariable('tpl', $this->getGlobalTemplateMock());
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();
    }

    public function testInstantiateObjectShouldReturnInstance(): void
    {
        /**
         * @runInSeparateProcess
         * @preserveGlobalState enabled
         */

        // Act
        $this->setGlobalVariable(
            'ui.factory',
            $this->getMockBuilder(\ILIAS\UI\Factory::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
        $this->setGlobalVariable(
            'ui.renderer',
            $this->getMockBuilder(\ILIAS\UI\Renderer::class)
                ->disableOriginalConstructor()
                ->getMock()
        );

        $instance = new assClozeTestGUI();
        $this->assertInstanceOf('assClozeTestGUI', $instance);
    }
}
