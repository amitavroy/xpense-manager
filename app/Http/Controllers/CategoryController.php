<?php

namespace App\Http\Controllers;

use App\Actions\AddCategoryAction;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $categories = Category::query()
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('categories/index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $category = new Category;

        return Inertia::render('categories/create', [
            'category' => $category,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request, AddCategoryAction $action): RedirectResponse
    {
        $data = $request->validated();

        $category = $action->execute($data);

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Category created successfully!',
        ]);

        return redirect()->route('categories.show', $category);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): Response
    {
        return Inertia::render('categories/show', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        $category->update($data);

        Inertia::flash('notification', [
            'type' => 'success',
            'message' => 'Category updated successfully!',
        ]);

        return redirect()->route('categories.show', $category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully');
    }
}
