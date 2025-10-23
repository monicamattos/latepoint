@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Settings | Plans')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <h4 class="fw-bold">Subscription Plans</h4>
        <p class="text-muted mb-0">Manage your LatePoint subscription plan and feature access.</p>
    </div>

    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @if(!$isSuperAdmin)
        <div class="col-lg-4 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Current subscription</h5>
                </div>
                <div class="card-body">
                    @if($subscription)
                        <p class="mb-1"><strong>Plan:</strong> {{ $subscription->plan?->name ?? __('N/A') }}</p>
                        <p class="mb-1"><strong>Status:</strong> {{ ucfirst($subscription->status) }}</p>
                        <p class="mb-1"><strong>Renews at:</strong> {{ optional($subscription->renews_at)->format('M d, Y') ?? __('Not scheduled') }}</p>
                        <p class="mb-3"><strong>Trial ends:</strong> {{ optional($subscription->trial_ends_at)->format('M d, Y') ?? __('No trial') }}</p>

                        <div class="d-flex gap-2">
                            <form method="post" action="{{ route('admin.settings-plans.cancel', $subscription) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm" {{ $subscription->status !== 'active' ? 'disabled' : '' }}>Cancel</button>
                            </form>
                            <form method="post" action="{{ route('admin.settings-plans.resume', $subscription) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm">Resume</button>
                            </form>
                        </div>
                    @else
                        <p class="text-muted">You do not currently have an active subscription. Select a plan below to get started.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-12">
            <div class="row">
                @forelse($plans as $plan)
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">{{ $plan->name }}</h5>
                                <small class="text-muted">{{ Str::upper($plan->currency) }} {{ number_format($plan->price, 2) }} / {{ $plan->billing_interval }}</small>
                            </div>
                            <div class="card-body">
                                <p>{{ $plan->description }}</p>
                                @php $features = $plan->feature_settings ?? []; @endphp
                                @if(count($features))
                                    <ul class="list-unstyled">
                                        @foreach($features as $feature => $enabled)
                                            <li><i class="bx bx-{{ $enabled ? 'check text-success' : 'x text-danger' }}"></i> {{ Str::headline($feature) }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted">No advanced features included.</p>
                                @endif
                            </div>
                            <div class="card-footer">
                                <form method="post" action="{{ route('admin.settings-plans.subscribe') }}">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="btn btn-primary w-100" {{ ($subscription && $subscription->plan_id === $plan->id) ? 'disabled' : '' }}>
                                        {{ ($subscription && $subscription->plan_id === $plan->id) ? 'Current Plan' : 'Choose Plan' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">No plans available from your provider yet.</div>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Create plan</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.settings-plans.store') }}" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" name="slug" placeholder="auto-generated if blank">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="price" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Currency</label>
                            <input type="text" class="form-control" name="currency" value="USD" maxlength="3">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Interval</label>
                            <select class="form-select" name="billing_interval">
                                <option value="monthly">Monthly</option>
                                <option value="weekly">Weekly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Interval count</label>
                            <input type="number" class="form-control" name="billing_interval_count" value="1" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Trial days</label>
                            <input type="number" class="form-control" name="trial_period_days" value="0" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">Status</label>
                            <input type="hidden" name="is_active" value="0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create-plan-active" checked>
                                <label class="form-check-label" for="create-plan-active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Feature toggles</label>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="feature_settings[advanced_roles]" value="1" id="feature-advanced-roles">
                                        <label class="form-check-label" for="feature-advanced-roles">Advanced roles</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="feature_settings[integrations_calendars]" value="1" id="feature-integrations-calendars">
                                        <label class="form-check-label" for="feature-integrations-calendars">Calendar integrations</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="feature_settings[integrations_meetings]" value="1" id="feature-integrations-meetings">
                                        <label class="form-check-label" for="feature-integrations-meetings">Meeting integrations</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary">Create plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Existing plans</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Interval</th>
                                    <th>Status</th>
                                    <th>Features</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ownedPlans as $plan)
                                    <tr>
                                        <td class="align-middle">{{ $plan->name }}</td>
                                        <td class="align-middle">{{ Str::upper($plan->currency) }} {{ number_format($plan->price, 2) }}</td>
                                        <td class="align-middle">{{ $plan->billing_interval_count }} {{ Str::headline($plan->billing_interval) }}</td>
                                        <td class="align-middle">
                                            @if($plan->is_active)
                                                <span class="badge bg-label-success">Active</span>
                                            @else
                                                <span class="badge bg-label-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            @php $features = $plan->feature_settings ?? []; @endphp
                                            @if(count($features))
                                                <small class="text-muted">{{ implode(', ', array_map(fn($label, $enabled) => ($enabled ? '' : 'No ') . Str::headline($label), array_keys($features), $features)) }}</small>
                                            @else
                                                <small class="text-muted">None</small>
                                            @endif
                                        </td>
                                        <td class="align-middle text-end">
                                            <div class="d-inline-flex gap-2">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-plan-{{ $plan->id }}" aria-expanded="false" aria-controls="edit-plan-{{ $plan->id }}">
                                                    Edit
                                                </button>
                                                <form method="post" action="{{ route('admin.settings-plans.destroy', $plan) }}" onsubmit="return confirm('Are you sure you want to delete this plan?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                            <div class="collapse mt-3" id="edit-plan-{{ $plan->id }}">
                                                <form method="post" action="{{ route('admin.settings-plans.update', $plan) }}" class="row g-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="col-md-4">
                                                        <label class="form-label">Name</label>
                                                        <input type="text" class="form-control" name="name" value="{{ $plan->name }}" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Slug</label>
                                                        <input type="text" class="form-control" name="slug" value="{{ $plan->slug }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Price</label>
                                                        <input type="number" step="0.01" min="0" class="form-control" name="price" value="{{ $plan->price }}" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Currency</label>
                                                        <input type="text" class="form-control" name="currency" value="{{ Str::upper($plan->currency) }}" maxlength="3">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Interval</label>
                                                        <select class="form-select" name="billing_interval">
                                                            <option value="weekly" {{ $plan->billing_interval === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                            <option value="monthly" {{ $plan->billing_interval === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                            <option value="yearly" {{ $plan->billing_interval === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Interval count</label>
                                                        <input type="number" class="form-control" name="billing_interval_count" value="{{ $plan->billing_interval_count }}" min="1">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Trial days</label>
                                                        <input type="number" class="form-control" name="trial_period_days" value="{{ $plan->trial_period_days }}" min="0">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label d-block">Status</label>
                                                        <input type="hidden" name="is_active" value="0">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit-plan-active-{{ $plan->id }}" {{ $plan->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="edit-plan-active-{{ $plan->id }}">Active</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Description</label>
                                                        <textarea class="form-control" name="description" rows="2">{{ $plan->description }}</textarea>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Feature toggles</label>
                                                        <div class="row g-2">
                                                            @php $existingFeatures = $plan->feature_settings ?? []; @endphp
                                                            <div class="col-md-3">
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" name="feature_settings[advanced_roles]" value="1" id="edit-feature-advanced-roles-{{ $plan->id }}" {{ ($existingFeatures['advanced_roles'] ?? false) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="edit-feature-advanced-roles-{{ $plan->id }}">Advanced roles</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" name="feature_settings[integrations_calendars]" value="1" id="edit-feature-integrations-calendars-{{ $plan->id }}" {{ ($existingFeatures['integrations_calendars'] ?? false) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="edit-feature-integrations-calendars-{{ $plan->id }}">Calendar integrations</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" name="feature_settings[integrations_meetings]" value="1" id="edit-feature-integrations-meetings-{{ $plan->id }}" {{ ($existingFeatures['integrations_meetings'] ?? false) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="edit-feature-integrations-meetings-{{ $plan->id }}">Meeting integrations</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 text-end">
                                                        <button type="submit" class="btn btn-sm btn-primary">Save changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">You have not created any plans yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
