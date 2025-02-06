<?php

namespace App\Http\Repositories\Admin;

class SyntheseRepository
{
    // /**
    //  * Summary of getTachesValidees
    //  *
    //  * @param  int  $userId
    //  *
    //  * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<\App\Models\Planning\Planning>
    //  */
    // public function getTachesValidees(int $userId)
    // {
    //     $archive = Archive::pluck('planning_id')->toArray();

    //     return Planning::where('user_id', $userId)
    //         ->where('is_validated', true)
    //         ->whereNotIn('id', $archive)
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(5);
    // }
}
