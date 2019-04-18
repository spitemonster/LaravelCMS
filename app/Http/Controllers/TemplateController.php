<?php

namespace App\Http\Controllers;
use App\Template;
use App\Field;
use Storage;
use Illuminate\Http\Request;
use App\Http\Requests\TemplateCreationRequest;

class TemplateController extends Controller
{
    public function createTemplate(TemplateCreationRequest $request)
    {
        // process template data
        $template = new Template;

        $template->name = $request->name;
        $template->template_id = $request->template_id;
        $template->save();

        foreach (json_decode($request->fields) as $field) {
            $f = new Field;

            $f->field_id = $field->field_id;
            $f->name = $field->name;
            $f->type = $field->type;
            $f->required = true;
            $f->template_id = $request->template_id;
            $f->save();
        }

        $boilerplate = '<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>

</body>
</html>';

        $template_name = strtolower(str_replace(' ', '_', $template->name));

        Storage::disk('resources')->put('/views/'. $template_name . '.blade.php', $boilerplate);
        return $request->fields;
    }

    public function deleteTemplate(Request $request) {
            if (!$request->query('template_id')) {
                return 'Bad boy';
            }

            $template = Template::where('template_id', $request->query('template_id'))->first();
            $fields = $template->fields()->get();

            foreach ($fields as $field) {
                $field->delete();
            }

            $template->delete();

            return 'All good';
    }

    public function getTemplate(Request $request) {
        if ($request->query('template_id')) {
            $template_id = $request->query('template_id');

            $template = [];

            $template[] = Template::where('template_id', $template_id)->first();
            $template[0]['fields'] = Template::where('template_id', $template_id)->first()->fields;

            return json_encode($template[0]);
        }

        return json_encode(Template::all());
    }

    public function updateTemplate(Request $request) {
        if (!$request->query('template_id')) {
            return 'Bad boy';
        }

        $template = Template::where('template_id', $request->query('template_id'))->first();
        $template->name = $request->name;

        foreach (json_decode($request->fields) as $field) {
            // dd($field->name);
            $f = Field::where('field_id', $field->field_id)->first();

            if(!$f) {
                $f = new Field;
                $f->type = $field->type;
                $f->field_id = $field->field_id;
                $f->template_id = $request->query('template_id');
            }

            $f->name = $field->name;
            $f->required = true;
            $f->save();
        }

        $template->save();

        return "Okay";
    }
}
