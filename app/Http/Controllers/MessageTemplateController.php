<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageTemplateRequest;
use App\Models\Message;
use App\Models\MessageTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MessageTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, \Closure $next) {
            Gate::authorize('templates.manage');

            return $next($request);
        });
    }

    /**
     * Screen 16 — saved message bodies with placeholders, reusable across
     * campaigns.
     */
    public function index(Request $request): View
    {
        $templates = MessageTemplate::query()
            ->where('client_id', $request->user()->actingClient()->id)
            ->orderByDesc('last_used_at')
            ->get();

        return view('templates.index', ['templates' => $templates]);
    }

    public function create(): View
    {
        return view('templates.create');
    }

    public function store(StoreMessageTemplateRequest $request): RedirectResponse
    {
        $this->save(new MessageTemplate, $request);

        return redirect()->route('templates.index')->with('status', 'Template saved.');
    }

    public function edit(Request $request, MessageTemplate $template): View
    {
        abort_unless($template->client_id === $request->user()->actingClient()->id, 403);

        return view('templates.edit', ['template' => $template]);
    }

    public function update(StoreMessageTemplateRequest $request, MessageTemplate $template): RedirectResponse
    {
        abort_unless($template->client_id === $request->user()->actingClient()->id, 403);

        $this->save($template, $request);

        return redirect()->route('templates.index')->with('status', 'Template saved.');
    }

    protected function save(MessageTemplate $template, StoreMessageTemplateRequest $request): void
    {
        $body = $request->validated('body');
        ['parts' => $parts] = Message::smsParts($body);

        $template->fill([
            'client_id' => $request->user()->actingClient()->id,
            'name' => $request->validated('name'),
            'body' => $body,
            'placeholders' => MessageTemplate::extractPlaceholders($body),
            'parts' => $parts,
        ])->save();
    }
}
