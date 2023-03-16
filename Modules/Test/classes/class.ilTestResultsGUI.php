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
 * Class ilTestResultsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 *
 * @ilCtrl_Calls ilTestResultsGUI: ilParticipantsTestResultsGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilMyTestResultsGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilTestEvalObjectiveOrientedGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilMyTestSolutionsGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilTestToplistGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilTestSkillEvaluationGUI
 */
class ilTestResultsGUI
{
    public const DEFAULT_CMD = 'show';

    /**
     * @var ilObjTest
     */
    protected $testObj;

    /**
     * @var ilTestQuestionSetConfig
     */
    protected $questionSetConfig;

    /**
     * @var ilTestAccess
     */
    protected $testAccess;

    /**
     * @var ilTestSession
     */
    protected $testSession;

    /**
     * @var ilTestTabsManager
     */
    protected $testTabs;

    /**
     * @var ilTestObjectiveOrientedContainer
     */
    protected $objectiveParent;
    private \ilGlobalTemplateInterface $main_tpl;

    /**
     * ilTestParticipantsGUI constructor.
     * @param ilObjTest $testObj
     */
    public function __construct(ilObjTest $testObj, ilTestQuestionSetConfig $questionSetConfig)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->testObj = $testObj;
        $this->questionSetConfig = $questionSetConfig;
    }

    /**
     * @return ilTestObjectiveOrientedContainer
     */
    public function getObjectiveParent(): ilTestObjectiveOrientedContainer
    {
        return $this->objectiveParent;
    }

    /**
     * @param ilTestObjectiveOrientedContainer $objectiveParent
     */
    public function setObjectiveParent($objectiveParent)
    {
        $this->objectiveParent = $objectiveParent;
    }

    /**
     * @return ilObjTest
     */
    public function getTestObj(): ilObjTest
    {
        return $this->testObj;
    }

    /**
     * @param ilObjTest $testObj
     */
    public function setTestObj($testObj)
    {
        $this->testObj = $testObj;
    }

    /**
     * @return ilTestQuestionSetConfig
     */
    public function getQuestionSetConfig(): ilTestQuestionSetConfig
    {
        return $this->questionSetConfig;
    }

    /**
     * @param ilTestQuestionSetConfig $questionSetConfig
     */
    public function setQuestionSetConfig($questionSetConfig)
    {
        $this->questionSetConfig = $questionSetConfig;
    }

    /**
     * @return ilTestAccess
     */
    public function getTestAccess(): ilTestAccess
    {
        return $this->testAccess;
    }

    /**
     * @param ilTestAccess $testAccess
     */
    public function setTestAccess($testAccess)
    {
        $this->testAccess = $testAccess;
    }

    /**
     * @return ilTestSession
     */
    public function getTestSession(): ilTestSession
    {
        return $this->testSession;
    }

    /**
     * @param ilTestSession $testSession
     */
    public function setTestSession($testSession)
    {
        $this->testSession = $testSession;
    }

    /**
     * @return ilTestTabsManager
     */
    public function getTestTabs(): ilTestTabsManager
    {
        return $this->testTabs;
    }

    /**
     * @param ilTestTabsManager $testTabs
     */
    public function setTestTabs($testTabs)
    {
        $this->testTabs = $testTabs;
    }

    /**
     * Execute Command
     */
    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $this->getTestTabs()->activateTab(ilTestTabsManager::TAB_ID_RESULTS);
        $this->getTestTabs()->getResultsSubTabs();

        switch ($DIC->ctrl()->getNextClass()) {
            case 'ilparticipantstestresultsgui':

                if (!$this->getTestAccess()->checkManageParticipantsAccess() && !$this->getTestAccess()->checkParticipantsResultsAccess()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_PARTICIPANTS_RESULTS);

                $gui = new ilParticipantsTestResultsGUI();
                $gui->setTestObj($this->getTestObj());
                $gui->setQuestionSetConfig($this->getQuestionSetConfig());
                $gui->setTestAccess($this->getTestAccess());
                $gui->setObjectiveParent($this->getObjectiveParent());
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case 'ilmytestresultsgui':

                if (!$this->getTestTabs()->needsMyResultsSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_MY_RESULTS);

                $gui = new ilMyTestResultsGUI();
                $gui->setTestObj($this->getTestObj());
                $gui->setTestAccess($this->getTestAccess());
                $gui->setTestSession($this->getTestSession());
                $gui->setObjectiveParent($this->getObjectiveParent());
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case 'iltestevalobjectiveorientedgui':

                if (!$this->getTestTabs()->needsLoResultsSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_LO_RESULTS);

                $gui = new ilTestEvalObjectiveOrientedGUI($this->getTestObj());
                $gui->setObjectiveOrientedContainer($this->getObjectiveParent());
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case 'ilmytestsolutionsgui':

                if (!$this->getTestTabs()->needsMySolutionsSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_MY_SOLUTIONS);

                $gui = new ilMyTestSolutionsGUI();
                $gui->setTestObj($this->getTestObj());
                $gui->setTestAccess($this->getTestAccess());
                $gui->setObjectiveParent($this->getObjectiveParent());
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case 'iltesttoplistgui':

                if (!$this->getTestTabs()->needsHighSoreSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_HIGHSCORE);

                $gui = new ilTestToplistGUI($this->getTestObj());
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case 'iltestskillevaluationgui':

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_SKILL_RESULTS);

                global $DIC; /* @var ILIAS\DI\Container $DIC */
                if ($this->getTestObj()->isDynamicTest()) {
                    $dynamicQuestionSetConfig = new ilObjTestDynamicQuestionSetConfig(
                        $DIC->repositoryTree(),
                        $DIC->database(),
                        $DIC['component.repository'],
                        $this->getTestObj()
                    );
                    $dynamicQuestionSetConfig->loadFromDb();
                    $questionList = new ilAssQuestionList($DIC->database(), $DIC->language(), $DIC['component.repository']);
                    $questionList->setParentObjId($dynamicQuestionSetConfig->getSourceQuestionPoolId());
                    $questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_ORIGINALS);
                } else {
                    $questionList = new ilAssQuestionList($DIC->database(), $DIC->language(), $DIC['component.repository']);
                    $questionList->setParentObjId($this->getTestObj()->getId());
                    $questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);
                }
                $questionList->load();

                $testSessionFactory = new ilTestSessionFactory($this->getTestObj());
                $testSession = $testSessionFactory->getSession();

                $gui = new ilTestSkillEvaluationGUI(
                    $DIC->ctrl(),
                    $DIC->tabs(),
                    $DIC->ui()->mainTemplate(),
                    $DIC->language(),
                    $DIC->database(),
                    $this->getTestObj()
                );
                $gui->setQuestionList($questionList);
                $gui->setTestSession($testSession);
                $gui->setObjectiveOrientedContainer($this->getObjectiveParent());

                $DIC->ctrl()->forwardCommand($gui);
                break;

            case strtolower(__CLASS__):
            default:

                $command = $DIC->ctrl()->getCmd(self::DEFAULT_CMD) . 'Cmd';
                $this->{$command}();
        }
    }

    protected function showCmd()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        if ($this->testObj->canShowTestResults($this->getTestSession())) {
            if ($this->objectiveParent->isObjectiveOrientedPresentationRequired()) {
                $DIC->ctrl()->redirectByClass('ilTestEvalObjectiveOrientedGUI');
            }

            $DIC->ctrl()->redirectByClass(array('ilMyTestResultsGUI', 'ilTestEvaluationGUI'));
        }

        $toolbar = $DIC->toolbar();
        $validator = new ilCertificateDownloadValidator();
        if ($validator->isCertificateDownloadable($DIC->user()->getId(), $this->getTestObj()->getId())) {
            $button = ilLinkButton::getInstance();
            $button->setCaption('certificate');
            $button->setUrl($DIC->ctrl()->getFormActionByClass(ilTestEvaluationGUI::class, 'outCertificate'));
            $toolbar->addButtonInstance($button);
        }

        $this->showNoResultsReportingMessage();
    }

    protected function showNoResultsReportingMessage()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $message = $DIC->language()->txt('tst_res_tab_msg_res_after_taking_test');

        switch ($this->testObj->getScoreReporting()) {
            case ilObjTest::SCORE_REPORTING_FINISHED:

                if ($this->testObj->hasAnyTestResult($this->getTestSession())) {
                    $message = $DIC->language()->txt('tst_res_tab_msg_res_after_finish_test');
                }

                break;

            case ilObjTest::SCORE_REPORTING_DATE:

                $date = new ilDateTime($this->testObj->getReportingDate(), IL_CAL_TIMESTAMP);

                if (!$this->testObj->hasAnyTestResult($this->getTestSession())) {
                    $message = sprintf(
                        $DIC->language()->txt('tst_res_tab_msg_res_after_date_no_res'),
                        ilDatePresentation::formatDate($date)
                    );
                    break;
                }

                $message = sprintf(
                    $DIC->language()->txt('tst_res_tab_msg_res_after_date'),
                    ilDatePresentation::formatDate($date)
                );
                break;

            case ilObjTest::SCORE_REPORTING_AFTER_PASSED:
                $message = $DIC->language()->txt('tst_res_tab_msg_res_after_test_passed');
                break;
        }

        $this->main_tpl->setOnScreenMessage('info', $message);
    }
}
