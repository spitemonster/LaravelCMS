<?php

namespace App\Http\Controllers;
use App\Field;
use App\Http\Requests\TemplateCreationRequest;
use App\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Storage;
use Uuid;
use Auth;

class TemplateController extends Controller
{
    // create
    public function createTemplate(TemplateCreationRequest $request)
    {
        // process template data
        $template = new Template;

        $template->name = $request->input('template_name');
        $template->template_id = Uuid::generate(4)->string;
        $template->user_id = $template->updated_user_id = Auth::user()->user_id;

        foreach ($request->input('template_fields') as $template_field) {
            $f = new Field;

            $f->field_id = Uuid::generate(4)->string;
            $f->name = $template_field['name'];
            $f->type = $template_field['type'];
            $f->required = $template_field['required'] ? true : false;
            $f->template_id = $template['template_id'];
            $f->save();
        }

        // generic template data
        $boilerplate =
"@include('header');

@include('footer');";

        $template->save();

        // write template boilerplate to file
        Storage::disk('resources')->put('/views/'. strtolower(str_replace(' ', '_', $template->name)) . '.blade.php', $boilerplate);

        $successMsg = array(
            'status' => 'success',
            'message' => 'Template successfully created'
        );

        return $successMsg;
    }

    // read
    public function getTemplate(Request $request) {
        if ($request->query('template_id')) {
            $template_id = $request->query('template_id');

            $template = Template::where('template_id', $template_id)->with('fields')->with('pages')->first();

            return $template;
        }

        return Template::with('pages')->get();
    }

    // update
    public function updateTemplate(Request $request) {
        $validator = Validator::make(['uuid' => $request->query('template_id')], ['uuid' => 'uuid']);

        if (!$request->query('template_id') || !$validator->passes()) {
            $errMsg = array(
                'status' => 'error',
                'message' => 'Requested action cannot be completed: a valid Template ID is required'
            );

            return $errMsg;
        }

        $template = Template::where('template_id', $request->query('template_id'))->first();

        if (!$template) {
            $errMsg = array(
                'status' => 'error',
                'message' => 'Requested action cannot be completed: A template with the selected ID cannot be found.'
            );

            return $errMsg;
        }

        // if the template's name is updated, make a new blade template with the new name to ensure any pages using the template still work but copy the content
        if ($template->name !== $request->input('name')) {
            $templateData = Storage::disk('resources')->get('/views/' . strtolower(str_replace(' ', '_', $template->name)) . '.blade.php');
            Storage::disk('resources')->put('/views/' . strtolower(str_replace(' ', '_', $request->input('name'))) . '.blade.php', $templateData );
            Storage::disk('resources')->delete('/views/' . strtolower(str_replace(' ', '_', $template->name)) . '.blade.php');
        }

        $template->name = $request->input('name');

        $template->updated_user_id = Auth::user()->user_id;

        if (!$template->user_id) {
            $template->user_id = $template->updated_user_id;
        }

        // loop through each field and save its new info
        foreach ($request->input('fields') as $field) {

            $f = Field::where('field_id', $field['field_id'])->with('values')->first();

            if(!$f) {
                $f = new Field;
                $f->type = $field['type'];
                $f->field_id = $field['field_id'] ? $field['field_id'] : Uuid::generate(4)->string;;
                $f->template_id = $request->query('template_id');
            }

            // loop through each FieldValue and save its new info. The only thing that can change ex post facto is the field name so we save that to be safe
            // this is to ensure that the name of the field when being rendered in the template file is consistent with what you name it when you create or modify it
            foreach($f->values as $value) {
                // wrap in an if to save from writing to the db if necessary
                if ($value->field_name !== $field['name']) {
                    $value->field_name = $field['name'];
                    $value->save();
                }
            }

            if ($f->name !== $field['name'] || $f->required !== $field['required']) {
                $f->name = $field['name'];
                $f->required = $field['required'];
                $f->save();
            }
        }

        if ($request->deleteFields) {
            foreach($request->deleteFields as $dField) {
                $f = Field::where('field_id', $dField);

                $f->delete();
            }
        }

        $template->save();

        $successMsg = array(
            'status' => 'success',
            'message' => 'Template successfully updated'
        );

        return $successMsg;
    }

    // delete
    public function deleteTemplate(Request $request) {
        // make sure the uuid given is a valid uuid
        $validator = Validator::make(['uuid' => $request->query('template_id')], ['uuid' => 'uuid']);

        // if not, throw an error and let them know they goofed
        if (!$request->query('template_id') || !$validator->passes()) {
            $errMsg = array(
                'status' => 'error',
                'message' => 'Requested action cannot be completed: a valid Template ID is required'
            );

            return $errMsg;
        }

        // if it is, continue from here
        // get the template, its name and pages using it
        $template = Template::where('template_id', $request->query('template_id'))->first();
        $template_name = strtolower(str_replace(' ', '_', $template->name));
        $pages = $template->pages;
        $fields = $template->fields()->get();

        // if the template file can't be found, throw an error
        if (!$template) {
            $errMsg = array(
                'status' => 'error',
                'message' => 'Requested action cannot be completed: A template with the selected ID cannot be found.'
            );

            return $errMsg;
        }

        // delete the pages first
        foreach ($pages as $page) {
            $page->delete();
        }

        // then fields
        foreach ($fields as $field) {
            $field->delete();
        }

        // then the template file so it doesn't sit around
        Storage::disk('resources')->delete('/views/'. $template_name . '.blade.php');

        // then delete the template itself
        $template->delete();

        // then we good
        $successMsg = array(
            'status' => 'success',
            'message' => 'Template successfully deleted'
        );

        return $successMsg;
    }
}
