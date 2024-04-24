<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
   public function getFaq(){
        try {
            $faqs = Faq::all();
            return response()->json(['faqs' => $faqs], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
   }
   public function insertFaq(Request $request){
        try{
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
            ]);
            $faq = new Faq();
            $faq->title = $request->title;
            $faq->description = $request->description;
            $faq->save();
            return response()->json(['message' => 'Faq added successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
   }
}
