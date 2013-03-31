<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Notes extends Controller_AbstractAdmin
{
	public static function validate($formValues) 
	{
		$validation = Validation::factory($formValues)
	               ->rule('noteText','not_empty', array(':value'));
		if (!$validation->check())
                {
			return $validation->errors();
		}
		return array();
	}

	public static function load_from_database($entityType, $entityId, $action = 'edit')
	{	
		$entity = Doctrine::em()->getRepository('Model_'.$entityType)->find($entityId);	
		$notesFormValues['notes']['currentNotes'] = array();
                foreach ($entity->notes as $n => $note)
                {
                        $notesFormValues['notes']['currentNotes'][$n] = array(
                                'id' => $note->id,
                                'noteText' => $note->noteText,
                                'createdAt' => $note->createdAt->format('Y-m-d H:i:s'),
                                'username' => $note->notetaker->username,
                        );
                }
                return $notesFormValues;
	}

	public static function load_form_template($action = 'edit')
	{
		$formTemplate['notes'] = array(
                        'title' => 'Notes',
                        'type' => 'fieldset',
                        'fields' => array(
                                'currentNotes' => array(
                                        'title' => '',
                                        'type' => 'table',
                                        'fields' => array(
                                                'id' => array('type' => 'hidden'),
                                                'noteText' => array('title' =>'Note', 'type' => 'statichidden'),
                                                'createdAt' => array('title' => 'Created At', 'type' => 'statichidden'),
                                                'username' => array('title' => 'Created By', 'type' => 'statichidden'),
                                        ),
                                ),
                        ),
                );
                if ($action == 'edit')
                {
			$userId = Doctrine::em()->getRepository('Model_User')->findOneByUsername(Auth::instance()->get_user())->id;
                        $formTemplate['notes']['fields']['currentNotes']['fields']['delete'] = array('title' => 'Delete', 'type' => 'button', 'onClick' => 'delete_note(document.getElementsByName(this.name.replace("delete","id")).item(0).value, document.forms[0].attributes["name"].value, document.forms[0].id.value);');
                        $formTemplate['notes']['fields']['newNote'] = array('title' => 'New note', 'type' => 'textarea', 'size' => 50);
			$formTemplate['notes']['fields']['addNewNote'] = array('title' => 'Add', 'type' => 'button', 'onClick' => 'add_note(document.forms[0].attributes["name"].value, document.forms[0].id.value, document.forms["Notes"].notes_newNote.value, "'.$userId.'");');
                }
                return $formTemplate;
	}

	public static function generate_form_javascript()
	{
		$javascript = "<script type=\"text/javascript\">
<!--
	function delete_note(note_id, annotated_type, annotated_id) {
		$.ajax(
                        {
                                url: '/admin/notes/'+note_id+'/delete',
                                type: 'get',
                                dataType: 'text',
                                success: function( strData ){
                                        $('#NotesMessage').html( strData );
					$.ajax(
                        			{
							url: '/admin/notes/table?entityType='+annotated_type+'&entityId='+annotated_id,
			                                type: 'get',
                        			        dataType: 'html',
							success: function( notesTable ){
                                        			$('#notes_currentNotes').html( notesTable );
							}
						}
					);
                                }
                        }
                ); 
	}
	
	function add_note(annotated_type, annotated_id, note_text, notetaker_id) {
		var urlencoded_note_text = encodeURIComponent(note_text)
		$.ajax(
			{
				url: '/admin/notes/create?entityType='+annotated_type+'&entityId='+annotated_id+'&noteText='+urlencoded_note_text+'&notetakerId='+notetaker_id,
				type: 'get',
				dataType: 'text',
				success: function( strData ){
					$('#NotesMessage').html( strData );
					 $.ajax(
                                                {
                                                        url: '/admin/notes/table?entityType='+annotated_type+'&entityId='+annotated_id,
                                                        type: 'get',
                                                        dataType: 'html',
                                                        success: function( notesTable ){
                                                                $('#notes_currentNotes').html( notesTable );
								$('#notes_newNote').val('');
                                                        }
                                                }
                                        );
				}
			}
		);
	}
-->
</script>
";
		return $javascript;
	}

	public function action_create() 
	{
		$this->auto_render = FALSE;
		$this->check_login("systemadmin");
		$noteText = $this->request->query('noteText');
		$decodedNoteText = urldecode($noteText);
		$note = Model_Note::build($this->request->query('entityType'), $this->request->query('entityId'), $decodedNoteText, $this->request->query('notetakerId'));
		$note->save();
		if ($note->id)
                {
                        echo "Note successfully added";
                }
                else
		{
                        echo "Error: Note could not be added";
                }
	}

	public function action_delete() 
	{
		$this->auto_render = FALSE;
		$this->check_login("systemadmin");
		$note = Doctrine::em()->getRepository('Model_Note')->find($this->request->param('id'));
                if (!is_object($note))
                {
                        echo "Error: Note not found";
			return;
                }
		$note->delete();
		$note2 = Doctrine::em()->getRepository('Model_Note')->find($this->request->param('id'));
		if (!is_object($note2))
		{
			echo "Note successfully deleted";
		}
		else
		{
			echo "Error: Note could not be deleted";
		} 
	}	
	
	public function action_table()
	{
		$this->auto_render = FALSE;
                $this->check_login("systemadmin");
                $entity = Doctrine::em()->getRepository('Model_'.$this->request->query('entityType'))->find($this->request->query('entityId'));
		$table = "          <tr class=\"tabletitle\"><th>Notes</th><th>Created At</th><th>Created By</th><th>Delete</th></tr>\n";
		$shade = "";
		$i = 0;
		foreach ($entity->notes as $note)
		{
			$table .= "<tr class=\"sowntablerow\"><input type=\"hidden\" value=\"{$note->id}\" name=\"notes_currentNotes_{$i}_id\"/><td$shade>{$note->noteText} <input type=\"hidden\" value=\"{$note->noteText}\" name=\"notes_currentNotes_{$i}_note\" /></td><td$shade>".$note->createdAt->format('Y-m-s H:i:s')." <input type=\"hidden\" value=\"".$note->createdAt->format('Y-m-s H:i:s')."\" name=\"notes_currentNotes_{$i}_createdAt\" /></td><td$shade>{$note->notetaker->username} <input type=\"hidden\" value=\"{$note->notetaker->username}\" name=\"notes_currentNotes_{$i}_username\" /></td><td$shade><input type=\"button\" name=\"notes_currentNotes_{$i}_delete\" value=\"Delete\" onClick='delete_note(document.getElementsByName(this.name.replace(\"delete\",\"id\")).item(0).value, document.forms[0].attributes[\"name\"].value, document.forms[0].id.value);' /></td></tr>\n";
			$i++;
			if (empty($shade)) 
			{
				$shade = " class=\"shade\"";
			}
			else
			{
				$shade = "";	
			}
		}
		echo $table;

	}	
}	
