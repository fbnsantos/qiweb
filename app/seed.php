<?php
// Conteúdo inicial, copiado de qivolt.com. Só é usado quando a base de dados é criada pela primeira vez;
// a partir daí tudo se edita no backoffice.
return [
    'settings' => [
        'site_name' => 'QIVOLT',
        'hero_eyebrow' => 'Robotics · Intelligent sensing',
        'site_tagline' => 'Precision. Autonomy. Purpose.',
        'hero_sub' => 'Where machines become sentient agents of terrain, orbit, and intent.',
        'site_description' => 'QIVOLT operates at the convergence of robotics and intelligent sensing, crafting systems that thrive where others fail: agriculture, forest, space and strategic autonomy.',
        'site_lang' => 'en',
        'footer_text' => '',
        'footer_links' => "PKMT Privacy Policy | /pkmt-privacy\nPKMT Terms of Use | /pkmt-terms\nFNdS | https://www.fbnsantos.com",
        'allow_indexing' => '1',
    ],
    'pages' => [
        [
            'slug' => 'home',
            'title' => 'QIVOLT',
            'description' => 'QIVOLT: robotics and intelligent sensing for agriculture, forest, space and strategic autonomy. Precision. Autonomy. Purpose.',
            'in_menu' => 1,
            'content' => <<<'HTML'
<p>QIVOLT operates at the convergence of <strong>robotics</strong> and <strong>intelligent sensing</strong>, crafting systems that thrive where others fail — in <strong>fields of cultivation</strong>, <strong>forests of uncertainty</strong>, <strong>the silence of space</strong>, and <strong>the fog of conflict</strong>.</p>
<p>Under the vision of <strong>FNdS</strong>, a pioneer in autonomous systems and machine ecosystems, QIVOLT is not just building tools — it is engineering presence. With decades of field-hardened knowledge, Filipe architects platforms that move, perceive, and decide with minimal dependence, maximum clarity, and tactical elegance.</p>
<h2>🌿 Agricultural and Forest Frontiers</h2>
<p>Not all growth is peaceful. In these complex biomes, our machines:</p>
<ul>
<li>Cultivate resilience</li>
<li>Navigate the unstructured</li>
<li>Decode biosignals from root to canopy</li>
</ul>
<h2>🛰 Orbital and Planetary Systems</h2>
<p>Beyond Earth, entropy reigns. Yet our systems:</p>
<ul>
<li>Mobilize on fractured terrain</li>
<li>Endure radiation and isolation</li>
<li>Adapt with embedded cognition</li>
</ul>
<h2>🛡 Strategic Autonomy</h2>
<p>In theatres where latency kills and stealth decides:</p>
<ul>
<li>We deploy robotic scouts with no return ticket</li>
<li>Establish silent, self-healing sensor webs</li>
<li>Fuse vision with intent beyond GNSS and command</li>
</ul>
<hr>
<p><strong>QIVOLT</strong> is not a company. It is a signal —<br>
A call to those who believe that robotics must go beyond automation,<br>
and IoT must sense not only data, but context and consequence.</p>
<blockquote>
<p>"The machine must not wait to be told what the terrain already knows." — <a href="https://www.fbnsantos.com">FNdS</a></p>
</blockquote>
HTML,
        ],
        [
            'slug' => 'pkmt_manual',
            'title' => 'PKMT-Agile Manager – Gmail Add-on Manual',
            'menu_label' => 'PKMT Manual',
            'description' => 'User guide for the PKMT-Agile Manager Gmail Add-on: view, create and manage ToDos directly from Gmail.',
            'in_menu' => 1,
            'menu_order' => 1,
            'content' => <<<'HTML'
<p>This document provides a user guide for the <strong>PKMT-Agile Manager Gmail Add-on</strong>, which allows users to view, create, and manage ToDos directly from Gmail.</p>
<hr>
<h2>📌 Overview</h2>
<p>The <strong>PKMT Gmail Add-on</strong> integrates with your Gmail inbox to help you:</p>
<ul>
<li>View your active ToDos</li>
<li>Create new ToDos from emails</li>
<li>Mark ToDos as complete</li>
<li>View task details</li>
<li>Open tasks directly in the PKMT system</li>
</ul>
<hr>
<h2>🚀 Getting Started</h2>
<ol>
<li><strong>Install the add-on</strong> from the Google Workspace Marketplace.</li>
<li>Open any Gmail message or your inbox.</li>
<li>Click the <strong>PKMT icon</strong> on the right sidebar to open the add-on panel.</li>
</ol>
<hr>
<h2>🔑 Authentication</h2>
<p>The add-on requires a <strong>PKMT API token</strong> to access your tasks.</p>
<h3>To configure your token:</h3>
<ol>
<li>Open the PKMT add-on.</li>
<li>If no token is stored, you'll be prompted to enter it.</li>
<li>Paste your token (from <a href="https://criis-projects.inesctec.pt/PK/index.php?tab=todos">PKMT → ToDos page</a>).</li>
<li>Click <strong>“Save Token”</strong>.</li>
</ol>
<blockquote>
<p>🔒 Once saved, the token is hidden by default. You can reveal or update it anytime.</p>
</blockquote>
<hr>
<h2>📋 Viewing Your Tasks</h2>
<p>Once authenticated:</p>
<ul>
<li>Your <strong>active ToDos</strong> are listed.</li>
<li>Each task shows its title and status.</li>
<li>Click a task to <strong>expand its details</strong>.</li>
</ul>
<p>Expanded view includes:</p>
<ul>
<li>Description</li>
<li>Due date</li>
<li>Responsible person</li>
<li>Button to open in PKMT</li>
<li>If created from an email, a link to the original message</li>
</ul>
<hr>
<h2>✅ Marking a Task as Complete</h2>
<p>Each task has a checkbox. Toggle it to:</p>
<ul>
<li>Mark as "completed"</li>
<li>Revert to "open"</li>
</ul>
<p>A confirmation message is shown upon success.</p>
<hr>
<h2>➕ Creating a New Task</h2>
<p>Click <strong>“➕ New Task”</strong> to open the creation form.</p>
<p>Fill in:</p>
<ul>
<li>Title</li>
<li>Description</li>
<li>Due date (in format <code>YYYY-MM-DD</code>)</li>
<li>Status (Open or In Progress)</li>
</ul>
<p>Click <strong>“Create Task”</strong> to save.</p>
<hr>
<h2>✉️ Creating a Task from an Email</h2>
<p>When viewing an email:</p>
<ul>
<li>Click <strong>“Create ToDo from this email”</strong></li>
<li>Subject and sender are auto-filled</li>
<li>Add/edit the details and click <strong>“Create Task”</strong></li>
</ul>
<p>The task will link back to the original email (if available).</p>
<hr>
<h2>⚙️ Settings</h2>
<p>You can:</p>
<ul>
<li>View if a token is configured</li>
<li>Edit or re-enter your token</li>
<li>Cancel token editing at any time</li>
</ul>
<hr>
<h2>🌐 Open PKMT in Browser</h2>
<p>At any time, you can click <strong>“Open PKMT-Agile Manager”</strong> to access the full web platform:
<a href="https://criis-projects.inesctec.pt/PK/index.php?tab=todos">https://criis-projects.inesctec.pt/PK/index.php?tab=todos</a></p>
<hr>
<h2>❓ Troubleshooting</h2>
<ul>
<li><strong>“Token not found”</strong> → Enter your token from the PKMT platform.</li>
<li><strong>“Error fetching tasks”</strong> → Check your token or network connection.</li>
<li><strong>Email link not working</strong> → The original message may no longer exist or be accessible.</li>
</ul>
<hr>
<h2>📩 Support</h2>
<p>For help or issues, contact: <strong>fbnsantos@gmail.com</strong></p>
HTML,
        ],
        [
            'slug' => 'pkmt-privacy',
            'title' => 'Privacy Policy – PKMT',
            'description' => 'How the PKMT application collects, uses, and protects personal information from its users.',
            'content' => <<<'HTML'
<p>This Privacy Policy describes how the <strong>PKMT</strong> application collects, uses, and protects personal information from its users.</p>
<h2>1. Data Collected</h2>
<p>The application may collect the following types of data:</p>
<ul>
<li>Name and email address (during login or registration)</li>
<li>IP address and usage data (for security or analytical purposes)</li>
<li>Any additional information voluntarily submitted by the user during use of the application</li>
</ul>
<h2>2. Purpose</h2>
<p>The collected data is used exclusively to:</p>
<ul>
<li>Enable access to and proper functioning of the application</li>
<li>Communicate with users when necessary</li>
<li>Improve the user experience</li>
</ul>
<h2>3. Data Sharing</h2>
<p>User data is <strong>not shared with third parties</strong>, except when required by law or for the technical operation of the application (e.g., authentication or hosting services).</p>
<h2>4. Storage and Security</h2>
<p>All data is stored on secure servers with appropriate technical safeguards. Data is not transferred outside the European Union unless adequate protection measures are in place.</p>
<h2>5. Cookies</h2>
<p>The application may use functional cookies to maintain user sessions. No third-party tracking cookies are used.</p>
<h2>6. User Rights</h2>
<p>Users have the right to:</p>
<ul>
<li>Access their personal data</li>
<li>Request corrections of incorrect information</li>
<li>Request deletion of their personal data</li>
</ul>
<p>For any data-related inquiries, users may contact: <strong>fbnsantos@gmail.com</strong></p>
<h2>7. Changes to This Policy</h2>
<p>We reserve the right to update this Privacy Policy at any time. The most recent version will always be available within the application.</p>
HTML,
        ],
        [
            'slug' => 'pkmt-terms',
            'title' => 'Terms of Use – PKMT',
            'description' => 'Terms of Use of the PKMT application.',
            'content' => <<<'HTML'
<p>By accessing and using the <strong>PKMT</strong> application ("the Service"), you agree to comply with and be bound by the following Terms of Use. If you do not agree with any part of these terms, please do not use the Service.</p>
<h2>1. Description of the Service</h2>
<p>PKMT is a web-based application designed to [insert purpose, e.g., manage project-related data, organize tasks, or other functionality]. Access to and use of the Service may require authentication and is provided as-is without any guarantees.</p>
<h2>2. Eligibility</h2>
<p>You must be at least 16 years old to use this Service. By using PKMT, you confirm that you meet this requirement.</p>
<h2>3. User Responsibilities</h2>
<p>By using the Service, you agree to:</p>
<ul>
<li>Use it only for lawful purposes</li>
<li>Not interfere with or disrupt the integrity or performance of the Service</li>
<li>Keep your access credentials secure and confidential</li>
<li>Not attempt to gain unauthorized access to any part of the system</li>
</ul>
<h2>4. Intellectual Property</h2>
<p>Unless otherwise stated, all content, code, and features within the Service are the property of the service owner and are protected by applicable copyright and intellectual property laws.</p>
<h2>5. Data and Privacy</h2>
<p>The collection and use of your personal data is governed by our <a href="/pkmt-privacy">Privacy Policy</a>. By using the Service, you agree to the terms of that policy.</p>
<h2>6. Availability and Changes</h2>
<p>The Service is provided "as is" and may be modified, suspended, or discontinued at any time without notice. We reserve the right to update or change these Terms at any time. Continued use of the Service after changes constitutes acceptance of the updated Terms.</p>
<h2>7. Limitation of Liability</h2>
<p>To the fullest extent permitted by law, the Service owner shall not be held liable for any direct, indirect, incidental, or consequential damages arising from the use or inability to use the Service.</p>
<h2>8. Contact</h2>
<p>For questions about these Terms, please contact: <strong>fbnsantos@gmail.com</strong></p>
HTML,
        ],
        [
            // Página de teste que existia em qivolt.com: fica acessível no mesmo endereço, fora do menu e do Google.
            'slug' => 'content',
            'title' => 'Hello World',
            'noindex' => 1,
            'content' => <<<'HTML'
<table>
<thead><tr><th>Language</th><th>Native Speakers (Approx.)</th><th>Hello</th></tr></thead>
<tbody>
<tr><td>English</td><td>1.5 billion</td><td>Hello</td></tr>
<tr><td>Mandarin Chinese</td><td>1.1 billion</td><td>你好 (Nǐ hǎo)</td></tr>
<tr><td>Hindi</td><td>600 million</td><td>नमस्ते (Namaste)</td></tr>
<tr><td>Spanish</td><td>500 million</td><td>Hola</td></tr>
<tr><td>French</td><td>300 million</td><td>Bonjour</td></tr>
<tr><td>Arabic</td><td>274 million</td><td>مرحبا (Marhaban)</td></tr>
<tr><td>Bengali</td><td>273 million</td><td>হ্যালো (Hyālō)</td></tr>
<tr><td>Russian</td><td>258 million</td><td>Здравствуйте (Zdravstvuyte)</td></tr>
<tr><td>Portuguese</td><td>258 million</td><td>Olá</td></tr>
<tr><td>Urdu</td><td>230 million</td><td>ہیلو (Hello)</td></tr>
</tbody>
</table>
HTML,
        ],
    ],
];
