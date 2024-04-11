<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Image;

class ImageController extends Controller {

    // public function uploadImage( Request $request ) {
    //     // dd( $request->all() );
    //     // dd( $request->file( 'image' ) );

    //     if ( $request->hasFile( 'image' ) ) {
    //         $image = $request->file( 'image' );
    //         $imageName = time().'.'.$image->getClientOriginalExtension();
    //         Storage::disk( 'public' )->putFileAs( 'images', $image, $imageName );

    //         $imageModel = new Image();
    //         $imageModel->filename = $imageName;
    //         $imageModel->save();

    //         return response()->json( [ 'success' => 'Image uploaded successfully', 'image_path' => '/images/' . $imageName ] );
    //     }

    //     return response()->json( [ 'error' => 'No image uploaded' ], 400 );
    // }


    public function uploadImage(Request $request)
{
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('/', $image, $imageName);

        $imageModel = new Image();
        $imageModel->filename = $imageName;
        $imageModel->save();

        return response()->json(['success' => 'Image uploaded successfully', 'image_path' => '/vendor/' . $imageName]);
    }

    return response()->json(['error' => 'No image uploaded'], 400);
}

    // public function show($id)
    // {
    //     $image = Image::find($id);
    //     $imagePath = "app/public/images/{$image->filename}";
    //     $imagePaths = "https://localhost:8000/" . $imagePath;

    //     dd($imagePaths);

    //     // If image path is not found, return a 404 response
    //     // if (!$imagePath) {
    //     //     return response()->json(['error' => 'Image not found'], 404);
    //     // }

    //     // Return the image file
    //     // return response()->file(storage_path('app/' . $imagePath));
    // }

//     public function getImage($id)
// {
//     // Construct the image path
//     $imagePath = public_path('uploads/' . $id);

//     // Check if the image exists
//     if (file_exists($imagePath)) {
//         // Return the image file
//         return response()->file($imagePath);
//     }

//     // If the image doesn't exist, return a 404 error
//     return response()->json(['error' => 'Image not found'], 404);
// }

public function getImage($id)
{
    // Retrieve the image filename based on the ID
    $image = Image::find($id);

    // If the image with the given ID exists
    if ($image) {
        // Construct the image path
        $imagePath = public_path('uploads/' . $image->filename);

        // Check if the image file exists
        if (file_exists($imagePath)) {
            return response()->file($imagePath);
        } else {
            // If the image file doesn't exist, return a 404 error
            return response()->json(['error' => 'Image file not found'], 404);
        }
    } else {
        // If the image with the given ID doesn't exist, return a 404 error
        return response()->json(['error' => 'Image not found'], 404);
    }

}
}
