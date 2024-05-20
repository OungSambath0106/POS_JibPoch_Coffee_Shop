<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = Menu::get();
        $menu_types = Menu::distinct('menutype')->pluck('menutype');
        // dd($menu_types);
        return view('menu.index', [
            'menus' => $menus,
            'menu_types' => $menu_types
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('menu.create');
    }

    public function uploadImage($image)
    {
        $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $image->getClientOriginalExtension();
        $image->move(public_path('storage/uploads/menus_photo'), $imageName);
        return $imageName;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'menuname' => 'required|string|max:100',
            'menutype' => 'required|string|max:50',
        ]);

        // Create a new User instance
        $menu = new Menu();
        $menu->menuname = $request->menuname;
        $menu->menutype = $request->menutype;
        $menu->price = $request->price;
        $menu->description = $request->description;

        // Set ishidden attribute based on the request
        $menu->ishidden = $request->has('ishidden') ? 1 : 0;

        if ($request->hasFile('image')) {
            $menu->image = $this->uploadImage($request->file('image'));
        }

        // Save the user
        $menu->save();

        return redirect('menus')->with('status', 'Menu Created Successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $menus = Menu::findOrFail($id);
        return view('menu.detail', compact('menus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $menus = Menu::findOrFail($id);
        return view('menu.edit', compact('menus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'menuname' => 'required|string|max:100',
            'menutype' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif', // Adjust validation rules for image uploads
        ]);

        $data = [
            'menuname' => $request->menuname,
            'menutype' => $request->menutype,
            'price' => $request->price,
            'description' => $request->description,
            'ishidden' => $request->ishidden == 'on' ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
            $menu->image = $this->uploadImage($request->file('image'));
        }

        $menu->update($data);

        return redirect('/menus')->with('status', 'Menu Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($menuId)
    {
        // $menu = Menu::findOrFail($menuId);
        // $menu->delete();

        // return redirect('/menus');

        $menu = Menu::findOrFail($menuId);

        // Check if the menu has an image
        if ($menu->image) {
            // Get the image path
            $imagePath = public_path('storage/uploads/menus_photo/' . $menu->image);

            // Check if the file exists
            if (file_exists($imagePath)) {
                // Attempt to delete the file
                if (unlink($imagePath)) {
                    // File deleted successfully
                    // Proceed to delete the menu
                    $menu->delete();
                    return redirect('/menus');
                } else {
                    // Error occurred while deleting the file
                    // Handle the error or log it for further investigation
                    dd('Error: Unable to delete file');
                }
            } else {
                // File does not exist at the specified path
                // Handle this case accordingly
                dd('Error: File does not exist');
            }
        } else {
            // Menu does not have an image
            // Proceed to delete the menu without attempting to delete the image
            $menu->delete();
            return redirect('/menus');
        }
    }
}
