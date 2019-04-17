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

        Storage::disk('resources')->put('/views/'. strtolower($template->name) . '.blade.php', $boilerplate);
        return $request->fields;
    }
}
