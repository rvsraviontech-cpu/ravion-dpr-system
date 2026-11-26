<section class="portal-hero rv-shared-hero" aria-label="Project introduction">
    <div class="rv-hero-image" aria-hidden="true"></div>
    <div class="rv-hero-content">
      <a class="rv-hero-back" href="{{ route('client.projects.index') }}">← &nbsp;All Projects</a>
      <div class="rv-hero-copy"><div class="eyebrow">Your project workspace</div><h1 class="page-title">{{ data_get($project, 'project_name') }}</h1><p class="lead">Project Reference: {{ data_get($project, 'project_code') }}</p></div>
      <span class="hero-status"><span class="hero-dot"></span>Secure access verified</span>
    </div>
</section>
