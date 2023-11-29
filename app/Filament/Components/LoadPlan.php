<?php

namespace App\Filament\Components;

use App\Models\Plan;
use Filament\Forms\Components\Component;

class LoadPlan extends Component
{
  public function render()
  {
    return view('filament::components.load-plan', [
      'plans' => $this->state('plans'),
    ]);
  }

  public function mount()
  {
    // Fetch tree data from the database and prepare it for display
    $plans = Plan::all(); // Retrieve the first tree data
    $formattedTree = $this->formatTree($plans); // Format data for display
    $this->state(['plans' => $formattedTree]);
  }

  // Method to format tree data for display
  private function formatTree($plans)
  {
    // Process and format tree data (convert to the needed structure)
    // For example, you might convert the retrieved JSON data to a hierarchical array.
    // Implement your logic to format the data according to your needs.
    $formattedData = $plans;

    return $formattedData;
  }
}
