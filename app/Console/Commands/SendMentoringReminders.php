<?php

namespace App\Console\Commands;

use App\Models\GoalMilestone;
use App\Models\MentoringMatch;
use App\Notifications\MatchActionNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-mentoring-reminders')]
#[Description('Send useful mentoring reminders and expire unanswered match proposals')]
class SendMentoringReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expired = MentoringMatch::with(['mentor', 'mentee'])->whereIn('status', ['proposed', 'pending-confirmation'])
            ->whereNotNull('expires_at')->where('expires_at', '<=', now())->get();

        foreach ($expired as $match) {
            $match->update(['status' => 'expired']);
            foreach ([$match->mentor, $match->mentee] as $participant) {
                $participant->notify(new MatchActionNotification($match->id, 'The mentoring programme', 'closed an unanswered match proposal', 'The confirmation period ended. Programme staff can review a new proposal.'));
            }
        }

        $reminders = MentoringMatch::with(['mentor', 'mentee'])->whereIn('status', ['proposed', 'pending-confirmation'])
            ->whereNull('confirmation_reminded_at')->whereBetween('expires_at', [now(), now()->addDays(2)])->get();

        foreach ($reminders as $match) {
            foreach ([$match->mentor, $match->mentee] as $participant) {
                $confirmed = (int) $participant->id === (int) $match->mentor_id ? $match->mentor_confirmed_at : $match->mentee_confirmed_at;
                if (! $confirmed) {
                    $participant->notify(new MatchActionNotification($match->id, 'The mentoring programme', 'sent a match confirmation reminder', 'Please review the proposal before '.$match->expires_at->format('j M Y').'.', true, 'reminder', 'confirmation'));
                }
            }
            $match->update(['confirmation_reminded_at' => now()]);
        }

        $milestones = GoalMilestone::with(['owner', 'goal.mentoringMatch.mentor', 'goal.mentoringMatch.mentee'])
            ->whereNotIn('status', ['completed'])
            ->whereNull('reminder_sent_at')
            ->whereNotNull('due_on')
            ->whereDate('due_on', '<=', today()->addDays(2))
            ->whereHas('goal.mentoringMatch', fn ($query) => $query->where('status', 'active'))
            ->get();

        foreach ($milestones as $milestone) {
            $match = $milestone->goal->mentoringMatch;
            $participants = $milestone->owner ? collect([$milestone->owner]) : collect([$match->mentor, $match->mentee]);
            $timing = $milestone->due_on->lt(today()) ? 'is overdue' : 'is due '.$milestone->due_on->format('j M Y');
            foreach ($participants as $participant) {
                $participant->notify(new MatchActionNotification($match->id, 'The mentoring programme', 'sent a milestone reminder', '“'.$milestone->title.'” '.$timing.'. Review or update the shared plan.', true, 'reminder', 'plan'));
            }
            $milestone->update(['reminder_sent_at' => now()]);
        }

        $this->info("Expired {$expired->count()} proposals, reminded {$reminders->count()} matches, and sent {$milestones->count()} milestone reminders.");

        return self::SUCCESS;
    }
}
