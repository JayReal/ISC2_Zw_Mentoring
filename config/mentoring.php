<?php

return [
    'proposal_expiry_days' => (int) env('MENTORING_PROPOSAL_EXPIRY_DAYS', 7),
    'styles' => [
        'structured' => ['label' => 'Structured and accountable', 'description' => 'Clear agendas, agreed actions and regular follow-up.'],
        'conversational' => ['label' => 'Conversational and reflective', 'description' => 'Open discussion with room to explore ideas and experience.'],
        'practical' => ['label' => 'Practical and project-based', 'description' => 'Exercises, examples or real work that can be reviewed together.'],
        'question-led' => ['label' => 'Question-led', 'description' => 'Questions that help the mentee reach considered conclusions.'],
        'advice-led' => ['label' => 'Direct guidance', 'description' => 'Experience-based suggestions and clear recommendations.'],
        'no-preference' => ['label' => 'No strong preference', 'description' => 'Agree a suitable approach together after the match begins.'],
    ],
    'support_levels' => [
        ['name' => 'Community mentoring', 'summary' => 'Group sessions, peer circles and office hours for common questions and early exploration.'],
        ['name' => 'Matched mentoring', 'summary' => 'A defined one-to-one or small-cohort relationship built around agreed outcomes.'],
        ['name' => 'Specialist mentoring', 'summary' => 'Scoped technical or leadership support where specialist experience and prerequisites matter.'],
    ],
    'pathways' => [
        'explore' => ['label' => 'Explore', 'group' => 'Getting established', 'description' => 'Clarify interests, understand cybersecurity and related professions, and identify a realistic first direction.'],
        'student-university' => ['label' => 'Student and University', 'group' => 'Getting established', 'description' => 'Connect academic learning with practical experience, professional networks and preparation for entry-level opportunities.'],
        'career-transition' => ['label' => 'Career Transition', 'group' => 'Getting established', 'description' => 'Translate existing experience into a credible move toward cybersecurity, governance, risk or a related professional role.'],
        'employment-employability' => ['label' => 'Employment and Employability', 'group' => 'Getting established', 'description' => 'Strengthen professional readiness, portfolios, interviews, workplace expectations and a sustainable opportunity-search strategy.'],
        'community-cyber-safety' => ['label' => 'Community and Cyber Safety', 'group' => 'Getting established', 'description' => 'Develop practical ways to improve cyber awareness, digital safety and community resilience.'],
        'professional-growth' => ['label' => 'Professional Growth', 'group' => 'Growing your contribution', 'description' => 'Set development priorities, broaden professional impact and make deliberate progress within an established career.'],
        'advanced-technical' => ['label' => 'Advanced Technical', 'group' => 'Growing your contribution', 'description' => 'Deepen specialist capability through scoped guidance, prerequisites and technically relevant goals.'],
        'leadership-management' => ['label' => 'Leadership and Management', 'group' => 'Growing your contribution', 'description' => 'Build judgement, communication, people leadership and the ability to lead security outcomes through others.'],
        'entrepreneurship' => ['label' => 'Entrepreneurship', 'group' => 'Growing your contribution', 'description' => 'Explore responsible cybersecurity services, business models, client value and the realities of building a sustainable venture.'],
        'life-professional-success' => ['label' => 'Life and Professional Success', 'group' => 'Growing your contribution', 'description' => 'Balance career direction, confidence, professional relationships and habits that support durable personal progress.'],
    ],
];
