<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\Inbox;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    private const PER_PAGE = 25;

    /**
     * Two piles, not one list of 233.
     *
     * Measured on the table as it stands: 197 of the 233 messages carry a
     * link, 168 have a body that is not unique, and 66 names account for all
     * of them — "DanielWeino" alone sent 70 from 14 addresses. Three survive
     * every rule in App\Support\Inbox, and one of those three is a real
     * customer asking whether she needs an appointment.
     *
     * The page used to show all 233 in date order across twelve pages, with
     * the message column cut off at the right edge of the screen and no way
     * to open one. Finding her meant reading the lot.
     *
     * Sorting runs over the whole table because duplication cannot be judged
     * a page at a time, and the paging is applied after. At 233 rows that is
     * one query and no measurable cost; if it ever becomes one, the verdict
     * belongs in a column on the row.
     */
    public function index(Request $request)
    {
        $pile = $request->get('pile') === 'junk' ? 'junk' : 'real';

        $split = Inbox::sort(ContactMessage::orderByDesc('created_at')->get());

        $chosen = $split[$pile];
        $page   = max(1, (int) $request->get('page', 1));

        return view('admin.contacts.index', [
            'messages' => $chosen->forPage($page, self::PER_PAGE),
            'pile'     => $pile,
            'page'     => $page,
            'pages'    => max(1, (int) ceil($chosen->count() / self::PER_PAGE)),
            'nReal'    => $split['real']->count(),
            'nJunk'    => $split['junk']->count(),
        ]);
    }

    public function destroy(ContactMessage $contact)
    {
        $contact->delete();

        return back()->with('status', 'Mensaje eliminado.');
    }
}
