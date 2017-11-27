<?php
/**
 * Class ilBiblAdminFieldTableGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblAdminFieldTableGUI extends ilTable2GUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	const TBL_ID = 'tbl_bibl_fields';
	/**
	 * @var \ilBiblAdminFactoryFacadeInterface
	 */
	protected $facade;
	/**
	 * @var ilBiblAdminFieldGUI
	 */
	protected $parent_obj;
	/**
	 * @var array
	 */
	protected $filter = [];
	/**
	 * @var int
	 */
	protected $position_index = 1;


	/**
	 * ilBiblAdminFieldTableGUI constructor.
	 *
	 * @param object                             $a_parent_obj
	 * @param \ilBiblAdminFactoryFacadeInterface $facade
	 */
	public function __construct($a_parent_obj, ilBiblAdminFactoryFacadeInterface $facade) {
		$this->facade = $facade;
		$this->parent_obj = $a_parent_obj;

		$this->setId(self::TBL_ID);
		$this->setPrefix(self::TBL_ID);
		$this->setFormName(self::TBL_ID);
		$this->ctrl()->saveParameter($a_parent_obj, $this->getNavParameter());

		parent::__construct($a_parent_obj);
		$this->parent_obj = $a_parent_obj;
		$this->setRowTemplate('tpl.bibl_administration_fields_list_row.html', 'Modules/Bibliographic');

		$this->setFormAction($this->ctrl()->getFormAction($this->parent_obj));

		$this->setExternalSorting(true);

		$this->setDefaultOrderField("identifier");
		$this->setDefaultOrderDirection("asc");
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);

		$this->initColumns();

		$this->addCommandButton(ilBiblAdminFieldGUI::CMD_SAVE, $this->lng()->txt("save"));

		$this->addFilterItems();
		$this->parseData();
	}


	protected function initColumns() {
		$this->addColumn($this->lng()->txt('order'), 'order');
		$this->addColumn($this->lng()->txt('identifier'), 'identifier');
		$this->addColumn($this->lng()->txt('translation'), 'translation');
		$this->addColumn($this->lng()->txt('standard'), 'is_standard_field');
		$this->addColumn($this->lng()->txt('actions'), '', '150px');
	}


	protected function addFilterItems() {
		$field = new ilTextInputGUI($this->lng()->txt('identifier'), 'identifier');
		$this->addAndReadFilterItem($field);
	}


	/**
	 * @param $field
	 */
	protected function addAndReadFilterItem(ilFormPropertyGUI $field) {
		$this->addFilterItem($field);
		$field->readFromSession();
		if ($field instanceof ilCheckboxInputGUI) {
			$this->filter[$field->getPostVar()] = $field->getChecked();
		} else {
			$this->filter[$field->getPostVar()] = $field->getValue();
		}
	}


	/**
	 * Fills table rows with content from $a_set.
	 *
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$field = $this->facade->fieldFactory()->findById($a_set['id']);

		$this->tpl->setCurrentBlock("POSITION");
		$this->tpl->setVariable('POSITION_VALUE', $field->getPosition() ? $field->getPosition() : $this->position_index);
		$this->tpl->setVariable('POSITION_NAME', "row_values[" . $a_set['id'] . "][position]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("IDENTIFIER");
		$this->tpl->setVariable('IDENTIFIER_VALUE', $field->getIdentifier());

		$this->tpl->setVariable('IDENTIFIER_NAME', "row_values[" . $field->getId()
		                                           . "][identifier]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("TRANSLATION");

		$this->tpl->setVariable('VAL_TRANSLATION', $this->facade->translationFactory()->translate($field));

		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("STANDARD");
		if ($field->getIsStandardField()) {
			$this->tpl->setVariable('IS_STANDARD_VALUE', $this->lng()->txt("standard"));
		} else {
			$this->tpl->setVariable('IS_STANDARD_VALUE', $this->lng()->txt("custom"));
		}

		$this->tpl->setVariable('IS_STANDARD_NAME', "row_values[" . $field->getId()
		                                            . "][is_standard_field]");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("DATA_TYPE");
		$this->tpl->setVariable('DATA_TYPE_NAME', "row_values[" . $field->getId() . "][data_type]");
		$this->tpl->setVariable('DATA_TYPE_VALUE', $this->facade->type()->getId());
		$this->tpl->parseCurrentBlock();
		$this->addActionMenu($field);

		$this->position_index ++;
	}


	/**
	 * @param \ilBiblFieldInterface $field
	 */
	protected function addActionMenu(ilBiblFieldInterface $field) {
		$selectionList = new ilAdvancedSelectionListGUI();
		$selectionList->setListTitle($this->lng->txt("actions"));
		$selectionList->setId($field->getIdentifier());

		$this->ctrl()
		     ->setParameter($this->parent_obj, ilBiblAdminRisFieldGUI::FIELD_IDENTIFIER, $field->getId());
		$this->ctrl()
		     ->setParameterByClass(ilBiblTranslationGUI::class, ilBiblAdminRisFieldGUI::FIELD_IDENTIFIER, $field->getId());

		$txt = $this->lng()->txt("translate");
		$selectionList->addItem($txt, "", $this->ctrl()
		                                       ->getLinkTargetByClass(ilBiblTranslationGUI::class, ilBiblTranslationGUI::CMD_DEFAULT));

		$this->tpl->setVariable('VAL_ACTIONS', $selectionList->getHTML());
	}


	protected function parseData() {
		$this->determineOffsetAndOrder();
		$this->determineLimit();

		$q = new ilBiblTableQueryInfo();

		foreach ($this->filter as $filter_key => $filter_value) {
			switch ($filter_key) {
				case 'identifier':
					$filter = new ilBiblTableQueryFilter();
					$filter->setFieldName($filter_key);
					$filter->setFieldValue('%' . $filter_value . '%');
					$filter->setOperator("LIKE");
					$q->addFilter($filter);
					break;
			}
		}

		$data = $this->facade->fieldFactory()
		                     ->filterAllFieldsForTypeAsArray($this->facade->type(), $q);

		$this->setData($data);
	}
}