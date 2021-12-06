<?php

namespace Civi\RcBase\Actions;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\ConfigContainer;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;
use \Civi\Api4\Contact;
use \Civi\Api4\Relationship;

use CRM_RcBase_ExtensionUtil as E;

/**
 * Based on the following actions:
 * https://lab.civicrm.org/extensions/action-provider/-/blob/master/Civi/ActionProvider/Action/Relationship/CreateOrUpdateRelationship.php
 * https://lab.civicrm.org/extensions/action-provider/-/blob/master/Civi/ActionProvider/Action/Contact/FindOrCreateOrganizationByName.php
 * https://lab.civicrm.org/extensions/action-provider/-/blob/master/Civi/ActionProvider/Action/Contact/UpdateCustomData.php
 */
class SetupOrganizationAndRelationship extends AbstractAction
{
    protected $relationshipTypes;
    protected $relationshipTypeIds;

    public function __construct()
    {
        parent::__construct();
        $relationshipTypesApi = civicrm_api3('RelationshipType', 'get', ['is_active' => 1, 'options' => ['limit' => 0]]);
        $this->relationshipTypes = [];
        $this->relationshipTypeIds = [];
        foreach ($relationshipTypesApi['values'] as $relType) {
            $this->relationshipTypes[$relType['name_a_b']] = $relType['label_a_b'];
            $this->relationshipTypeIds[$relType['name_a_b']] = $relType['id'];
        }
    }

    /**
     * Returns the specification of the configuration options for the actual action.
     *
     * @return SpecificationBag
     */
    public function getConfigurationSpecification()
    {
        return new SpecificationBag([
            new Specification('relationship_type_id', 'String', E::ts('Relationship type'), true, null, null, $this->relationshipTypes, false),
        ]);
    }

    /**
     * Returns the specification of the configuration options for the actual action.
     *
     * @return SpecificationBag
     */
    public function getParameterSpecification()
    {
        $specs = new SpecificationBag([
            /**
             * The parameters given to the Specification object are:
             * @param string $name
             * @param string $dataType
             * @param string $title
             * @param bool $required
             * @param mixed $defaultValue
             * @param string|null $fkEntity
             * @param array $options
             * @param bool $multiple
             */
            new Specification('contact_id_a', 'Integer', E::ts('Contact ID'), true, null, null, null, false),
            new Specification('organization_name', 'String', E::ts('Organization name'), true, null, null, null, false),
        ]);
        $config = ConfigContainer::getInstance();
        $customGroups = $config->getCustomGroupsForEntities(['Organization']);
        foreach ($customGroups as $customGroup) {
            if (!empty($customGroup['is_active'])) {
                $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
            }
        }
        return $specs;
    }

    /**
     * Returns the specification of the output parameters of this action.
     *
     * This function could be overriden by child classes.
     *
     * @return SpecificationBag
     */
    public function getOutputSpecification()
    {
        return new SpecificationBag([
            new Specification('organization_id', 'Integer', E::ts('Organization ID')),
            new Specification('relationship_id', 'Integer', E::ts('Relationship ID')),
        ]);
    }

    /**
     * Run the action
     *
     * @param ParameterInterface $parameters
     *   The parameters to this action.
     * @param ParameterBagInterface $output
     *   The parameters this action can send back
     * @return void
     */
    protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output)
    {
        $orgName = $parameters->getParameter('organization_name');

        // Find the contact with the given name. Set limit to
        // be able to check the duplication also.
        $contacts = Contact::get(false)
            ->addSelect('id')
            ->addWhere('contact_type', '=', 'Organization')
            ->addWhere('organization_name', '=', $orgName)
            ->setLimit(2)
            ->execute();
        // If contact is not found or we have at least 2 with
        // the given name, create a new one.
        if (count($contacts) !== 1) {
            // create new organization
            $contacts = Contact::create(false)
                ->addValue('contact_type', 'Organization')
                ->addValue('organization_name', $orgName)
                ->execute();
        }
        $organization = $contacts->first();
        $output->setParameter('organization_id', $organization['id']);
        // Update the custom fields on case of given.
        // The api params needs to be checked before the update. If we have nothing, we don't need
        // to delete the current ones.
        $apiParams = CustomField::getCustomFieldsApiParameter($parameters, $this->getParameterSpecification());
        if (count($apiParams)) {
            $apiParams['id'] = $organization['id'];
            civicrm_api3('Contact', 'create', $apiParams);
        }

        // relationship between the organization and the contact.
        $relationshipTypeId = $this->relationshipTypeIds[$this->configuration->getParameter('relationship_type_id')];
        $relationship = Relationship::get(false)
            ->addWhere('contact_id_a', '=', $parameters->getParameter('contact_id_a'))
            ->addWhere('contact_id_b', '=', $organization['id'])
            ->addWhere('relationship_type_id', '=', $relationshipTypeId)
            ->setLimit(1)
            ->execute()
            ->first();
        if (is_null($relationship)) {
            $relationship = Relationship::create(false)
                ->addValue('contact_id_a', $parameters->getParameter('contact_id_a'))
                ->addValue('contact_id_b', $organization['id'])
                ->addValue('relationship_type_id', $relationshipTypeId)
                ->execute()
                ->first();
        }
        $output->setParameter('relationship_id', $relationship['id']);
    }
}
