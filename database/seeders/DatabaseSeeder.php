<?php

namespace Database\Seeders;

use App\Models\Cluster;
use App\Models\ProgrammeCycle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        ProgrammeCycle::create(['name' => 'Controlled Pilot', 'starts_on' => '2026-10-01', 'ends_on' => '2027-01-31', 'status' => 'draft', 'target_participants' => 75]);

        $clusters = [
            ['Cyber Foundations and Career Discovery', 'Non-technical and technical beginners, cyber concepts, pathways, vocabulary and digital safety.'],
            ['Governance, Risk, Compliance and Audit', 'GRC, internal audit, risk, policy, legal and board engagement.'],
            ['Security Operations and Defensive Security', 'SOC, detection, incident response, threat hunting and blue team.'],
            ['Offensive Security and Security Testing', 'Authorised security testing, vulnerability management and red team practice.'],
            ['Cloud, Infrastructure and Identity', 'Cloud security, IAM, network security and infrastructure resilience.'],
            ['Application, Data and AI Security', 'AppSec, DevSecOps, data protection, AI security and secure development.'],
            ['Leadership, Strategy and Cyber Management', 'Security leadership, business alignment, budgeting and executive communication.'],
            ['Advanced Technical Guild', 'Deep technical practice, research, labs, architecture reviews and specialist peer mentoring.'],
            ['University and Student Communities', 'Campus-based circles linked to the Chapter and professional mentors.'],
            ['Career, Employment and Professional Success', 'CVs, interviews, portfolios, workplace skills and career planning.'],
            ['Life, Leadership and Personal Effectiveness', 'Confidence, communication, goals, resilience and professional identity.'],
            ['Cyber for Everyone', 'Practical cyber safety and organisational awareness for non-cyber professionals and communities.'],
        ];

        foreach ($clusters as $index => [$name, $description]) {
            Cluster::create(['name' => $name, 'slug' => str($name)->slug(), 'description' => $description, 'display_order' => $index + 1]);
        }
    }
}
