<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Field;

class FieldController extends Controller
{
    public function deleteField(Request $request) {
        if (!$request->query('field_id')) {
            return response('Requested action cannot be completed; you must include a valid Field ID', 403);
        }

        $field = Field::where('field_id', $request->query('field_id'))->with('values')->first();

        foreach ($field->values as $value) {
            $value->delete();
        }

        $field->delete();

        return 200;
    }
}
