<?php

/**
 * Test API Save class
 *
 * @group headless
 */
class CRM_RcBase_Api_SaveHeadlessTest extends CRM_RcBase_Test_BaseHeadlessTestCase
{
    /**
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSaveTagWithNotTaggedContact()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Create tag
        $tag = [
            'name' => 'Test tag',
        ];
        $tag_id = $this->cvApi4Create('Tag', $tag);

        // Number of entity tags already in DB
        $all_entity_tag_old = $this->cvApi4Get('EntityTag', ['id']);

        // Save tag to contact
        $entity_tag_id = CRM_RcBase_Api_Save::tagContact($contact_id, $tag_id);

        $all_entity_tag_new = $this->cvApi4Get('EntityTag', ['id']);

        $this->assertCount(
            count($all_entity_tag_old) + 1,
            $all_entity_tag_new,
            'No new entity tag created'
        );

        // Get from DB
        $id = $this->cvApi4Get('EntityTag', ['id'], [
            'entity_table=civicrm_contact',
            "entity_id=${contact_id}",
            "tag_id=${tag_id}",
        ]);
        $this->assertCount(1, $id, 'Not one result returned for "id"');

        // Check valid ID
        $this->assertSame($id[0]['id'], $entity_tag_id, 'Bad entity tag ID returned');
    }

    /**
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testSaveTagWithTaggedContact()
    {
        // Create contact
        $contact_id = $this->individualCreate([], self::getNextContactSequence());

        // Create tag
        $tag = [
            'name' => 'Another test tag',
        ];
        $tag_id = $this->cvApi4Create('Tag', $tag);

        // Add tag to contact
        $entity_tag = [
            'entity_table' => 'civicrm_contact',
            'entity_id' => $contact_id,
            'tag_id' => $tag_id,
        ];
        $entity_tag_id = $this->cvApi4Create('EntityTag', $entity_tag);

        // Number of entity tags already in DB
        $all_entity_tag_old = $this->cvApi4Get('EntityTag', ['id']);

        // Save tag to contact
        $entity_tag_id_save = CRM_RcBase_Api_Save::tagContact($contact_id, $tag_id);

        $all_entity_tag_new = $this->cvApi4Get('EntityTag', ['id']);

        $this->assertCount(
            count($all_entity_tag_old),
            $all_entity_tag_new,
            'Additional entity tag created'
        );
        // Check valid ID
        $this->assertNull($entity_tag_id_save, 'Not null returned when no tagging was needed');
    }
}
