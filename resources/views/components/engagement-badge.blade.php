@php
    $engagement = $job->engagement_type ?? \App\Models\Job::ENGAGEMENT_JOB_REQUEST;
    $engagementSlug = str_replace('_', '-', $engagement);
    $engagementLabel = match ($engagement) {
        \App\Models\Job::ENGAGEMENT_SERVICE_LISTING => 'Tangazo la Huduma',
        \App\Models\Job::ENGAGEMENT_SERVICE_BOOKING => 'Agizo la Huduma',
        default => 'Ombi la Kazi',
    };
@endphp
<span class="adm-pill adm-pill--{{ $engagementSlug }}" title="Aina ya kazi">{{ $engagementLabel }}</span>
