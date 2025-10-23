<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Agenda | Vista Booking</title>
    <link rel="icon" href="{{ asset('assets/img/short-logo.png') }}">
    <style>
        :root {
            color-scheme: light;
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --brand-primary: #1f3c88;
            --brand-accent: #4f6fec;
            --brand-muted: #6c7aa0;
            --surface: #ffffff;
            --surface-alt: #f4f6fb;
            --border-radius: 20px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top, #eef2ff 0%, #e2e8ff 55%, #dde4ff 100%);
            color: #1a265a;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px clamp(16px, 5vw, 48px);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(31, 60, 136, 0.1);
        }

        .site-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--brand-primary);
        }

        .site-brand img {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            object-fit: cover;
        }

        .site-nav {
            display: flex;
            gap: 18px;
            font-weight: 600;
        }

        .site-nav a {
            padding: 10px 18px;
            border-radius: 999px;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .site-nav a:hover,
        .site-nav a:focus-visible {
            background: rgba(79, 111, 236, 0.12);
            color: var(--brand-primary);
        }

        main {
            padding: clamp(24px, 6vw, 64px);
        }

        .hero {
            max-width: 960px;
            margin: 0 auto clamp(32px, 8vw, 80px);
            text-align: center;
        }

        .hero h1 {
            margin: 0 0 16px;
            font-size: clamp(2.2rem, 4vw, 3.4rem);
            letter-spacing: -0.02em;
        }

        .hero p {
            margin: 0;
            font-size: clamp(1rem, 2.4vw, 1.25rem);
            color: var(--brand-muted);
            line-height: 1.7;
        }

        .booking-section {
            max-width: 1200px;
            margin: 0 auto;
        }

        .booking-wrapper {
            background: rgba(255, 255, 255, 0.92);
            border-radius: clamp(20px, 3vw, 28px);
            box-shadow: 0 30px 60px rgba(31, 60, 136, 0.1);
            padding: clamp(24px, 5vw, 48px);
        }

        form#booking-form {
            display: flex;
            flex-direction: column;
            gap: clamp(24px, 4vw, 36px);
        }

        .booking-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.8fr) minmax(0, 1fr);
            gap: clamp(24px, 4vw, 40px);
        }

        .form-column {
            display: flex;
            flex-direction: column;
            gap: clamp(24px, 4vw, 40px);
        }

        .form-block {
            background: var(--surface);
            border-radius: clamp(16px, 3vw, 22px);
            padding: clamp(18px, 3vw, 28px);
            box-shadow: 0 20px 40px rgba(31, 60, 136, 0.08);
        }

        .form-block header {
            margin-bottom: 16px;
        }

        .form-block h2 {
            margin: 0;
            font-size: clamp(1.1rem, 2.5vw, 1.6rem);
        }

        .form-block .subtitle {
            margin: 4px 0 0;
            font-size: 0.95rem;
            color: var(--brand-muted);
        }

        .option-group {
            display: grid;
            gap: 12px;
        }

        .option-category {
            margin-top: 12px;
        }

        .option-category h3 {
            margin: 12px 0 10px;
            font-size: 0.95rem;
            color: var(--brand-muted);
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .option-card {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 12px;
            align-items: start;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(79, 111, 236, 0.15);
            transition: border 0.2s ease, box-shadow 0.2s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .option-card:hover,
        .option-card:focus-within {
            border-color: rgba(79, 111, 236, 0.4);
            box-shadow: 0 12px 24px rgba(79, 111, 236, 0.12);
        }

        .option-card input[type="radio"] {
            margin-top: 4px;
            width: 18px;
            height: 18px;
            accent-color: var(--brand-accent);
        }

        .option-title {
            font-weight: 600;
        }

        .option-meta,
        .option-description,
        .option-bio {
            display: block;
            margin-top: 4px;
            font-size: 0.9rem;
            color: var(--brand-muted);
        }

        .option-bio {
            margin-top: 8px;
            line-height: 1.5;
        }

        .grid-two {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        label.field-label {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 6px;
            display: block;
        }

        .field-control {
            width: 100%;
            border-radius: 14px;
            border: 1px solid rgba(31, 60, 136, 0.2);
            padding: 14px 16px;
            font-size: 1rem;
            transition: border 0.2s ease, box-shadow 0.2s ease;
        }

        .field-control:focus {
            outline: none;
            border-color: var(--brand-accent);
            box-shadow: 0 0 0 4px rgba(79, 111, 236, 0.15);
        }

        textarea.field-control {
            min-height: 120px;
            resize: vertical;
        }

        .summary-column {
            background: var(--surface-alt);
            border-radius: clamp(16px, 3vw, 22px);
            padding: clamp(18px, 3vw, 28px);
            box-shadow: inset 0 0 0 1px rgba(79, 111, 236, 0.12);
            display: flex;
            flex-direction: column;
            gap: 18px;
            position: sticky;
            top: 120px;
            height: fit-content;
        }

        .summary-column h2 {
            margin: 0;
            font-size: 1.2rem;
        }

        .summary-list {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 14px;
        }

        .summary-item {
            display: grid;
            gap: 4px;
        }

        .summary-label {
            font-size: 0.85rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--brand-muted);
        }

        .summary-value {
            font-weight: 600;
            font-size: 1rem;
        }

        .summary-note {
            margin: 0;
            font-size: 0.85rem;
            color: var(--brand-muted);
            line-height: 1.5;
        }

        .submit-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-accent));
            color: #fff;
            padding: 14px 24px;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .submit-button:hover,
        .submit-button:focus-visible {
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(79, 111, 236, 0.3);
        }

        .alert {
            border-radius: 14px;
            padding: 14px 18px;
            font-weight: 600;
        }

        .alert-success {
            background: rgba(76, 187, 116, 0.15);
            color: #0f5132;
            border: 1px solid rgba(76, 187, 116, 0.4);
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.1);
            color: #842029;
            border: 1px solid rgba(220, 53, 69, 0.35);
        }

        .alert-error ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        @media (max-width: 1024px) {
            .booking-grid {
                grid-template-columns: 1fr;
            }

            .summary-column {
                position: static;
            }
        }

        @media (max-width: 600px) {
            .site-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .site-nav {
                width: 100%;
                justify-content: flex-start;
                gap: 12px;
            }
        }
    </style>
</head>

<body>
    <header class="site-header">
        <div class="site-brand">
            <img src="{{ asset('assets/img/short-logo.png') }}" alt="Vista Booking">
            <span>Vista Booking</span>
        </div>
        <nav class="site-nav">
            <a href="https://rgaobe.com.br" target="_blank" rel="noopener">Home</a>
            <a href="{{ $loginDestination }}">{{ $loginLabel }}</a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>{{ $steps['select_service']['title'] ?? 'Agende seu atendimento' }}</h1>
            @if (!empty($steps['select_service']['description']))
                <p>{{ $steps['select_service']['description'] }}</p>
            @else
                <p>Escolha o serviço, profissional e horário ideais para você. O restante fica por nossa conta.</p>
            @endif
        </section>

        <section class="booking-section" id="agendamento">
            <div class="booking-wrapper">
                @if (session('booking_success'))
                    <div class="alert alert-success">{{ session('booking_success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        <span>Verifique as informações abaixo:</span>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="booking-form" method="POST" action="{{ route('bookings.store') }}">
                    @csrf
                    <div class="booking-grid">
                        <div class="form-column">
                            <section class="form-block">
                                <header>
                                    <h2>{{ $steps['select_location']['title'] ?? 'Escolha o local' }}</h2>
                                    @if (!empty($steps['select_location']['description']))
                                        <p class="subtitle">{{ $steps['select_location']['description'] }}</p>
                                    @endif
                                </header>

                                @if ($locations->isEmpty())
                                    <p class="option-description">Nenhum local cadastrado no momento. Este passo será pulado.</p>
                                @else
                                    <div class="option-group" data-location-options>
                                        @foreach ($locationsByCategory as $categoryId => $group)
                                            @php
                                                $categoryName = $locationCategoryNames[$categoryId] ?? null;
                                            @endphp
                                            @if ($customSettings['showLocationCategories'] && $categoryName)
                                                <div class="option-category">
                                                    <h3>{{ $categoryName }}</h3>
                                                </div>
                                            @endif
                                            @foreach ($group as $index => $location)
                                                <label class="option-card">
                                                    <input type="radio" name="location_id" value="{{ $location->id }}" @checked(old('location_id') == $location->id) {{ $loop->first && $loop->parent->first ? 'required' : '' }}>
                                                    <div>
                                                        <span class="option-title">{{ $location->name }}</span>
                                                        @if ($location->full_address)
                                                            <span class="option-meta">{{ $location->full_address }}</span>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        @endforeach
                                    </div>
                                @endif
                            </section>

                            <section class="form-block">
                                <header>
                                    <h2>{{ $steps['select_service']['title'] ?? 'Selecione o serviço' }}</h2>
                                    @if (!empty($steps['select_service']['sub_title']))
                                        <p class="subtitle">{{ $steps['select_service']['sub_title'] }}</p>
                                    @endif
                                </header>

                                <div class="option-group" data-service-options>
                                    @foreach ($servicesByCategory as $categoryId => $group)
                                        @php
                                            $categoryName = $serviceCategoryNames[$categoryId] ?? null;
                                        @endphp
                                        @if ($customSettings['showServiceCategories'] && $categoryName)
                                            <div class="option-category">
                                                <h3>{{ $categoryName }}</h3>
                                            </div>
                                        @endif
                                        @foreach ($group as $service)
                                            <label class="option-card">
                                                <input type="radio" name="service_id" value="{{ $service->id }}" @checked(old('service_id') == $service->id) {{ $loop->first && $loop->parent->first ? 'required' : '' }}>
                                                <div>
                                                    <span class="option-title">{{ $service->name }}</span>
                                                    @php
                                                        $config = $service->configuration();
                                                    @endphp
                                                    @if (!empty($config['short_description']))
                                                        <span class="option-description">{{ $config['short_description'] }}</span>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    @endforeach
                                </div>
                            </section>

                            <section class="form-block">
                                <header>
                                    <h2>{{ $steps['select_agent']['title'] ?? 'Escolha o profissional' }}</h2>
                                    @if (!empty($steps['select_agent']['sub_title']))
                                        <p class="subtitle">{{ $steps['select_agent']['sub_title'] }}</p>
                                    @endif
                                </header>
                                <div class="option-group" data-agent-options>
                                    <p class="option-description">Selecione um serviço para ver os profissionais disponíveis.</p>
                                </div>
                            </section>

                            <section class="form-block">
                                <header>
                                    <h2>{{ $steps['select_date_time']['title'] ?? 'Data e horário' }}</h2>
                                    @if (!empty($steps['select_date_time']['description']))
                                        <p class="subtitle">{{ $steps['select_date_time']['description'] }}</p>
                                    @endif
                                </header>
                                <div class="grid-two">
                                    <div>
                                        <label class="field-label" for="start_date">Data</label>
                                        <input type="date" id="start_date" name="start_date" class="field-control" value="{{ old('start_date') }}" required>
                                    </div>
                                    <div>
                                        <label class="field-label" for="start_time">Horário</label>
                                        <input type="time" id="start_time" name="start_time" class="field-control" value="{{ old('start_time') }}" required>
                                    </div>
                                </div>
                                @if ($customSettings['showTimezoneSelector'])
                                    <div style="margin-top: 16px;">
                                        <label class="field-label" for="timezone">Fuso horário</label>
                                        <select id="timezone" name="timezone" class="field-control">
                                            @foreach ($timezoneOptions as $timezone)
                                                <option value="{{ $timezone }}" @selected(old('timezone', $defaultTimezone) === $timezone)>{{ $timezone }}</option>
                                            @endforeach
                                        </select>
                                        @if ($customSettings['showTimezoneInfo'])
                                            <span class="option-meta" style="margin-top: 6px; display:block;">Os horários serão convertidos automaticamente para o fuso informado.</span>
                                        @endif
                                    </div>
                                @else
                                    <input type="hidden" name="timezone" value="{{ $defaultTimezone }}">
                                @endif
                            </section>

                            <section class="form-block">
                                <header>
                                    <h2>{{ $steps['enter_information']['title'] ?? 'Seus dados' }}</h2>
                                    @if (!empty($steps['enter_information']['description']))
                                        <p class="subtitle">{{ $steps['enter_information']['description'] }}</p>
                                    @endif
                                </header>
                                <div class="grid-two">
                                    <div>
                                        <label class="field-label" for="first_name">Nome</label>
                                        <input type="text" id="first_name" name="first_name" class="field-control" value="{{ old('first_name') }}" required>
                                    </div>
                                    <div>
                                        <label class="field-label" for="last_name">Sobrenome</label>
                                        <input type="text" id="last_name" name="last_name" class="field-control" value="{{ old('last_name') }}">
                                    </div>
                                </div>
                                <div class="grid-two" style="margin-top: 16px;">
                                    <div>
                                        <label class="field-label" for="email">E-mail</label>
                                        <input type="email" id="email" name="email" class="field-control" value="{{ old('email') }}" required>
                                    </div>
                                    <div>
                                        <label class="field-label" for="phone">Telefone</label>
                                        <input type="tel" id="phone" name="phone" class="field-control" value="{{ old('phone') }}">
                                    </div>
                                </div>
                                <div style="margin-top: 16px;">
                                    <label class="field-label" for="notes">Observações</label>
                                    <textarea id="notes" name="notes" class="field-control" placeholder="Compartilhe alguma informação importante (opcional)">{{ old('notes') }}</textarea>
                                </div>
                            </section>
                        </div>

                        <aside class="summary-column">
                            <h2>{{ $steps['verify_order_details']['title'] ?? 'Resumo do agendamento' }}</h2>
                            <ul class="summary-list">
                                <li class="summary-item">
                                    <span class="summary-label">Local</span>
                                    <span class="summary-value" data-summary-location>Selecione um local</span>
                                </li>
                                <li class="summary-item">
                                    <span class="summary-label">Serviço</span>
                                    <span class="summary-value" data-summary-service>Selecione um serviço</span>
                                </li>
                                <li class="summary-item">
                                    <span class="summary-label">Profissional</span>
                                    <span class="summary-value" data-summary-agent>Selecione um profissional</span>
                                </li>
                                <li class="summary-item">
                                    <span class="summary-label">Data e horário</span>
                                    <span class="summary-value" data-summary-when>Defina data e horário</span>
                                </li>
                                @if ($customSettings['showDurationInMinutes'])
                                    <li class="summary-item">
                                        <span class="summary-label">Duração</span>
                                        <span class="summary-value" data-summary-duration>--</span>
                                    </li>
                                @endif
                                <li class="summary-item">
                                    <span class="summary-label">Valor estimado</span>
                                    <span class="summary-value" data-summary-price>--</span>
                                </li>
                            </ul>
                            <button type="submit" class="submit-button">Confirmar agendamento</button>
                            @unless ($customSettings['skipVerifyStep'])
                                <p class="summary-note">{{ $steps['verify_order_details']['description'] ?? 'Revise as informações antes de enviar o pedido de agendamento.' }}</p>
                            @endunless
                        </aside>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script>
        const bookingData = {
            services: @json($servicePayload),
            agents: @json($agentsPayload),
            allowAnyAgent: @json($customSettings['allowAnyAgent']),
            showAgentBio: @json($customSettings['showAgentBio']),
            hideAgentInfo: @json($customSettings['hideAgentInfo']),
            showDuration: @json($customSettings['showDurationInMinutes']),
            oldSelection: {
                serviceId: @json(old('service_id')),
                agentId: @json(old('agent_id')),
                locationId: @json(old('location_id'))
            }
        };

        const formatter = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        const formatMinutes = (totalMinutes) => {
            if (!totalMinutes || Number.isNaN(totalMinutes)) {
                return '--';
            }

            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;

            if (hours === 0) {
                return `${minutes} min`;
            }

            if (minutes === 0) {
                return hours === 1 ? '1 hora' : `${hours} horas`;
            }

            return `${hours}h ${minutes}min`;
        };

        const formatWhen = (dateValue, timeValue) => {
            if (!dateValue || !timeValue) {
                return 'Defina data e horário';
            }

            try {
                const [year, month, day] = dateValue.split('-').map(Number);
                const [hour, minute] = timeValue.split(':').map(Number);
                const date = new Date(Date.UTC(year, month - 1, day, hour, minute));
                return date.toLocaleString('pt-BR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (e) {
                return `${dateValue} ${timeValue}`;
            }
        };

        const findService = (serviceId) => {
            return bookingData.services.find((service) => String(service.id) === String(serviceId));
        };

        const findAgent = (agentId) => {
            return bookingData.agents.find((agent) => String(agent.id) === String(agentId));
        };

        document.addEventListener('DOMContentLoaded', () => {
            const serviceRadios = document.querySelectorAll('input[name="service_id"]');
            const locationRadios = document.querySelectorAll('input[name="location_id"]');
            const agentContainer = document.querySelector('[data-agent-options]');
            const summaryLocation = document.querySelector('[data-summary-location]');
            const summaryService = document.querySelector('[data-summary-service]');
            const summaryAgent = document.querySelector('[data-summary-agent]');
            const summaryWhen = document.querySelector('[data-summary-when]');
            const summaryPrice = document.querySelector('[data-summary-price]');
            const summaryDuration = document.querySelector('[data-summary-duration]');
            const dateInput = document.querySelector('input[name="start_date"]');
            const timeInput = document.querySelector('input[name="start_time"]');

            const updateLocationSummary = () => {
                const selected = document.querySelector('input[name="location_id"]:checked');
                if (!selected) {
                    summaryLocation.textContent = 'Selecione um local';
                    return;
                }

                const option = selected.closest('.option-card');
                const title = option?.querySelector('.option-title');
                const meta = option?.querySelector('.option-meta');
                summaryLocation.textContent = meta ? `${title?.textContent ?? ''} • ${meta.textContent}`.trim() : title?.textContent ?? 'Local selecionado';
            };

            const updateWhenSummary = () => {
                summaryWhen.textContent = formatWhen(dateInput.value, timeInput.value);
            };

            const updatePriceSummary = (service, agentId) => {
                if (!service) {
                    summaryPrice.textContent = '--';
                    if (summaryDuration) {
                        summaryDuration.textContent = '--';
                    }
                    return;
                }

                const basePrice = service.base_price;
                let price = basePrice;

                if (agentId && agentId !== 'any' && service.agent_prices) {
                    const custom = service.agent_prices[agentId];
                    if (typeof custom === 'number') {
                        price = custom;
                    }
                }

                if (price === null || Number.isNaN(price)) {
                    summaryPrice.textContent = 'Sob consulta';
                } else {
                    summaryPrice.textContent = formatter.format(price);
                }

                if (summaryDuration) {
                    summaryDuration.textContent = formatMinutes(service.duration_minutes);
                }
            };

            const renderAgents = (service) => {
                agentContainer.innerHTML = '';

                if (!service) {
                    agentContainer.innerHTML = '<p class="option-description">Selecione um serviço para ver os profissionais disponíveis.</p>';
                    summaryAgent.textContent = 'Selecione um profissional';
                    updatePriceSummary(null, null);
                    return;
                }

                const allowedIds = Array.isArray(service.offered_agent_ids) && service.offered_agent_ids.length > 0
                    ? service.offered_agent_ids.map(String)
                    : bookingData.agents.map((agent) => String(agent.id));

                const agents = bookingData.agents.filter((agent) => allowedIds.includes(String(agent.id)));

                if (agents.length === 0) {
                    agentContainer.innerHTML = '<p class="option-description">Nenhum profissional disponível para este serviço no momento.</p>';
                    summaryAgent.textContent = 'Aguardando disponibilidade';
                    updatePriceSummary(service, null);
                    return;
                }

                if (bookingData.allowAnyAgent && agents.length > 0) {
                    const anyLabel = document.createElement('label');
                    anyLabel.className = 'option-card';
                    anyLabel.innerHTML = `
                        <input type="radio" name="agent_id" value="any">
                        <div>
                            <span class="option-title">Qualquer profissional disponível</span>
                            <span class="option-meta">Escolheremos automaticamente o melhor profissional para você.</span>
                        </div>
                    `;
                    agentContainer.appendChild(anyLabel);
                }

                agents.forEach((agent) => {
                    const label = document.createElement('label');
                    label.className = 'option-card';

                    const radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = 'agent_id';
                    radio.value = agent.id;
                    label.appendChild(radio);

                    const wrapper = document.createElement('div');
                    const title = document.createElement('span');
                    title.className = 'option-title';
                    title.textContent = agent.name;
                    wrapper.appendChild(title);

                    if (!bookingData.hideAgentInfo && agent.title) {
                        const meta = document.createElement('span');
                        meta.className = 'option-meta';
                        meta.textContent = agent.title;
                        wrapper.appendChild(meta);
                    }

                    if (bookingData.showAgentBio && agent.bio) {
                        const bio = document.createElement('span');
                        bio.className = 'option-bio';
                        bio.textContent = agent.bio;
                        wrapper.appendChild(bio);
                    }

                    label.appendChild(wrapper);
                    agentContainer.appendChild(label);
                });

                const radios = agentContainer.querySelectorAll('input[name="agent_id"]');
                if (radios.length > 0) {
                    radios[0].setAttribute('required', 'required');
                }

                const previous = bookingData.oldSelection.agentId;
                if (previous) {
                    const match = agentContainer.querySelector(`input[name="agent_id"][value="${previous}"]`);
                    if (match) {
                        match.checked = true;
                        summaryAgent.textContent = previous === 'any'
                            ? 'Qualquer profissional disponível'
                            : (findAgent(previous)?.name ?? 'Profissional selecionado');
                        updatePriceSummary(service, previous);
                        bookingData.oldSelection.agentId = null;
                    }
                } else {
                    summaryAgent.textContent = 'Selecione um profissional';
                    updatePriceSummary(service, null);
                }
            };

            const handleServiceChange = (event) => {
                const serviceId = event.target.value;
                const service = findService(serviceId);

                summaryService.textContent = service ? service.name : 'Selecione um serviço';
                bookingData.oldSelection.agentId = null;
                renderAgents(service);
            };

            serviceRadios.forEach((radio) => {
                radio.addEventListener('change', handleServiceChange);
            });

            locationRadios.forEach((radio) => {
                radio.addEventListener('change', updateLocationSummary);
            });

            agentContainer.addEventListener('change', (event) => {
                if (event.target.name !== 'agent_id') {
                    return;
                }

                const serviceId = document.querySelector('input[name="service_id"]:checked')?.value;
                const service = serviceId ? findService(serviceId) : null;
                const value = event.target.value;

                if (value === 'any') {
                    summaryAgent.textContent = 'Qualquer profissional disponível';
                } else {
                    const agent = findAgent(value);
                    summaryAgent.textContent = agent ? agent.name : 'Profissional selecionado';
                }

                updatePriceSummary(service, value);
            });

            dateInput.addEventListener('change', updateWhenSummary);
            timeInput.addEventListener('change', updateWhenSummary);

            // Initialise selections
            if (!document.querySelector('input[name="service_id"]:checked') && serviceRadios.length > 0) {
                serviceRadios[0].checked = true;
            }

            const initialServiceId = document.querySelector('input[name="service_id"]:checked')?.value || bookingData.oldSelection.serviceId;
            if (initialServiceId) {
                const service = findService(initialServiceId);
                if (service) {
                    summaryService.textContent = service.name;
                    renderAgents(service);
                    bookingData.oldSelection.serviceId = null;
                }
            }

            if (!document.querySelector('input[name="agent_id"]:checked')) {
                updatePriceSummary(findService(document.querySelector('input[name="service_id"]:checked')?.value), null);
            }

            if (bookingData.oldSelection.agentId) {
                const match = agentContainer.querySelector(`input[name="agent_id"][value="${bookingData.oldSelection.agentId}"]`);
                if (match) {
                    match.checked = true;
                    match.dispatchEvent(new Event('change'));
                }
            }

            updateLocationSummary();
            updateWhenSummary();
        });
    </script>
</body>

</html>
