<?php

namespace Souravmsh\LaravelTracker\View\Components;

use Illuminate\View\Component;

class DashboardWidget extends Component
{
    public string $title;
    public string $description; 

    /**
     * Create a new component instance.
     *
     * @param string|null $title
     * @param string|null $description
     */
    public function __construct(
        ?string $title = null,
        ?string $description = null
    ) {
        $this->title       = $title ?? 'Laravel Tracker - Dashboard Widget';
        $this->description = $description ?? 'This widget provides an overview of the Laravel Tracker dashboard, displaying key metrics and insights about your application’s traffic and user interactions.';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('tracker::dashboard', [
            'title'       => $this->title,
            'description' => $this->description
        ]);
    }
}