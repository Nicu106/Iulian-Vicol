#!/usr/bin/env python3
"""Hours, prompts and tokens for this project, read from Claude Code's own logs.

Claude Code writes every session to ~/.claude/projects/<project>/<session>.jsonl:
each prompt with its time, each reply with its token usage. Sub-agents write their
own files under <session>/subagents/. This reads all of them.

"Active time" adds up the gaps between consecutive events that are shorter than
--gap minutes (default 15). A longer silence is a break, not work. It measures the
time the session was being worked in, not the owner's thinking between prompts.

    python3 tools/usage/report.py                 # everything
    python3 tools/usage/report.py --since 2026-09-01
    python3 tools/usage/report.py --gap 5
"""
import argparse, collections, datetime as dt, glob, json, os

ap = argparse.ArgumentParser()
ap.add_argument('--project', default=os.path.expanduser('~/.claude/projects/-var-www-motorclass'))
ap.add_argument('--since', default=None, help='YYYY-MM-DD, local date')
ap.add_argument('--gap', type=int, default=15, help='minutes of silence that count as a break')
ap.add_argument('--tz', type=float, default=2, help='hours from UTC for the day buckets (Spain summer = 2)')
a = ap.parse_args()
TZ = dt.timezone(dt.timedelta(hours=a.tz))
since = dt.date.fromisoformat(a.since) if a.since else None

def when(s): return dt.datetime.fromisoformat(s.replace('Z', '+00:00'))

times, prompts, seen = [], [], set()
usage = collections.Counter()
for path in glob.glob(f'{a.project}/*.jsonl') + glob.glob(f'{a.project}/*/subagents/**/*.jsonl', recursive=True):
    sub = '/subagents/' in path
    with open(path) as f:
        for line in f:
            try: e = json.loads(line)
            except ValueError: continue
            t = e.get('timestamp')
            if not t: continue
            t = when(t)
            if since and t.astimezone(TZ).date() < since: continue
            times.append(t)
            if not sub and e.get('type') == 'user' and not e.get('isSidechain') and not e.get('isMeta'):
                c = e.get('message', {}).get('content')
                if isinstance(c, str) and (e.get('origin', {}).get('kind') == 'human' or e.get('promptSource') == 'typed'):
                    prompts.append(t)
            if e.get('type') == 'assistant':
                m = e.get('message', {}); u = m.get('usage'); mid = m.get('id')
                if u and mid and mid not in seen:
                    seen.add(mid)
                    for k in ('input_tokens', 'output_tokens', 'cache_read_input_tokens', 'cache_creation_input_tokens'):
                        usage[k] += u.get(k) or 0

times.sort()
per_day = collections.Counter()
for x, y in zip(times, times[1:]):
    d = y - x
    if d <= dt.timedelta(minutes=a.gap):
        per_day[x.astimezone(TZ).date()] += d.total_seconds()
p_day = collections.Counter(t.astimezone(TZ).date() for t in prompts)

total = sum(per_day.values()) / 3600
print(f'| Zi | Ore active | Prompturi |\n|---|---|---|')
for d in sorted(per_day):
    print(f'| {d} | {per_day[d]/3600:.1f} | {p_day.get(d, 0)} |')
print(f'| **Total** | **{total:.1f}** | **{len(prompts)}** |\n')
print(f'Pauză considerată: > {a.gap} min · {len(per_day)} zile lucrate')
print(f'Tokeni generați (output): {usage["output_tokens"]:,}')
print(f'Tokeni citiți din cache: {usage["cache_read_input_tokens"]:,}')
print(f'Tokeni scriși în cache: {usage["cache_creation_input_tokens"]:,}')
print(f'Tokeni input necache: {usage["input_tokens"]:,}')
