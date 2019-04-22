<?php

namespace App\Http\Controllers;
use App\Field;
use App\Http\Requests\TemplateCreationRequest;
use App\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Storage;
use Uuid;

class TemplateController extends Controller
{
    // create
    public function createTemplate(TemplateCreationRequest $request)
    {
        // process template data
        $template = new Template;

        $template->name = $request->input('template_name');
        $template->template_id = Uuid::generate(4)->string;

        foreach (json_decode($request->input('template_fields')) as $template_field) {
            $f = new Field;

            $f->field_id = Uuid::generate(4)->string;
            $f->name = $template_field->name;
            $f->type = $template_field->type;
            $f->required = $template_field->required ? true : false;
            $f->template_id = $template->template_id;
            $f->save();
        }

        $boilerplate = '
            <!DOCTYPE html>
            <html>
                <head>
                    <title></title>
                </head>
                <body>

                </body>
            </html>
        ';

        $template_name = strtolower(str_replace(' ', '_', $template->name));

        $template->save();
        Storage::disk('resources')->put('/views/'. $template_name . '.blade.php', $boilerplate);

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

            $template = Template::where('template_id', $template_id)->with('fields')->first();
            // $template[0]['fields'] = Template::where('template_id', $template_id)->first()->fields;

            return $template;
        }

        return json_encode(Template::all());
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

        $template->name = $request->input('name');

        foreach ($request->input('fields') as $field) {
            $f = Field::where('field_id', $field['field_id'])->first();

            if(!$f) {
                $f = new Field;
                $f->type = $field['type'];
                $f->field_id = $field['field_id'];
                $f->template_id = $request->query('template_id');
            }

            $f->name = $field['name'];
            $f->required = $field['required'];
            $f->save();
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

            $fields = $template->fields()->get();

            foreach ($fields as $field) {
                $field->delete();
            }

            Storage::disk('resources')->delete('/views/'. $template->name . '.blade.php');

            $template->delete();

            $successMsg = array(
                'status' => 'success',
                'message' => 'Template successfully deleted'
            );

            return $successMsg;
    }
}
