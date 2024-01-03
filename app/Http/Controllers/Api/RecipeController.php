<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Recipe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeResource;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use Symfony\Component\HttpFoundation\Response;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recipes = Recipe::all();
        return RecipeResource::collection($recipes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $recipe = new Recipe();
            $recipe->user_id = $user->id;
            $recipe->recipe_name = $request->recipe_name;
            $recipe->caption = $request->caption;
            $recipe->ingredients = $request->ingredients;
            $recipe->steps = $request->steps;
            $recipe->image = $request->image;
            $recipe->calorie = $request->calorie;
            $recipe->servings = $request->servings;
            $recipe->time = $request->time;
            $recipe->save();
            $recipe->categories()->sync($request->categories);

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Recipe Created',
                'data' => $recipe,
            ];
        } catch (Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $recipe = Recipe::find($request->id);
        return new RecipeResource($recipe);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recipe $recipe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $recipe = Recipe::where('id', $request->id)->first();

        if (!empty($recipe)) {
            if ($request->user()->id !== $recipe->user_id) {
                return [
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'You are not authorized to update this recipe.',
                    'data' => []
                ];
            } else {
                try {
                    $recipe->update([
                        'recipe_name' => $request->recipe_name,
                        'caption' => $request->caption,
                        'ingredients' => $request->ingredients,
                        'steps' => $request->steps,
                        'image' => $request->image,
                        'calorie' => $request->calorie,
                        'servings' => $request->servings,
                        'time' => $request->time,
                    ]);
                    $recipe->categories()->sync($request->categories);
                    return [
                        'status' => Response::HTTP_OK,
                        'message' => 'Recipe Updated',
                        'data' => $recipe,
                    ];
                } catch (Exception $e) {
                    return [
                        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                        'message' => $e->getMessage(),
                        'data' => [],
                    ];
                }
            }
        }
        return [
            'status' => Response::HTTP_NOT_FOUND,
            'message' => 'User not Found',
            'data' => []
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $user = $request->user();
            $recipe = Recipe::find($request->id);

            if (!$recipe) {
                return [
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Recipe not found',
                    'data' => [],
                ];
            }

            if ($user->id !== $recipe->user_id) {
                return [
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'You do not have permission to delete this recipe',
                    'data' => [],
                ];
            }

            $recipe->categories()->detach();
            $recipe->delete();

            return [
                'status' => Response::HTTP_OK,
                'message' => 'Recipe deleted successfully',
                'data' => [],
            ];
        } catch (Exception $e) {
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }
}
