<?php
$html = Livewire\Livewire::mount('admin.user-management');
if (strpos($html, 'speeda-pagination') !== false) {
    echo "PAGINATION IS IN THE HTML!\n";
} else {
    echo "PAGINATION IS MISSING FROM HTML!\n";
}
file_put_contents('test_livewire.html', $html);
