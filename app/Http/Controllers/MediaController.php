<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Uuid;
use Storage;
use File;
use App\Media;

class MediaController extends Controller
{

    public function createMedia(Request $request) {
        // accept file
        $fn = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = $request->file('file')->getClientOriginalExtension();
        $files = Media::all();

        // function tests if a given filename is already taken
        // if it's not, it returns an empty string
        // if not, it returns a hypen with an iterative number
        function testFileName($f, $it = 1) {
            $test = ($it > 1) ? $f . '-' . $it : $f;

            $file = Media::where('filename', $test)->first();

            if (!$file) {
                return ($it > 1) ? '-' . $it : '';
            }

            return testFileName($f, ($it + 1));
        }

        $filename = $fn . testFileName($fn);
        $file_id = Uuid::generate(4)->string;

        $file = $request->file('file')->storeAs(
            '/', $filename . '.' . $ext, 'media');

        $media = new Media;

        $media->url = '/media/' . $file;
        $media->filename = $filename;
        $media->file_id = $file_id;
        $media->ext = $ext;

        $media->save();

        $data = array(
            'status' => 'success',
            'message' => 'File successfully uploaded',
            'url' => '/media/' . $file
        );

        return $data;
    }

    public function getMedia(Request $request) {
        if ($request->query('media_id')) {
            return Media::where('file_id', $request->query('media_id'));
        }

        return Media::all();
    }

    public function deleteMedia(Request $request) {
        if (!$request->query('media_id')) {
            return 400;
        }

        $m = Media::where('file_id', $request->query('media_id'))->first();

        Storage::disk('media')->delete($m->filename . '.jpg');

        $m->delete();

        return Media::all();
    }

}
