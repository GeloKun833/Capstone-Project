<li class="xfer-row">
    <div>
        <div class="xfer-subject">{{ $primary }}</div>
        @if($meta && $meta !== '—')
            <span class="xfer-meta">{{ $meta }}</span>
        @endif
    </div>
    <div>
        <label class="visually-hidden" for="replacement-{{ $row['key'] }}">Transfer {{ $primary }}</label>
        <select id="replacement-{{ md5($row['key']) }}" class="form-control" name="replacements[{{ $row['key'] }}]" @disabled($blocked || ! $row['has_eligible'])>
            <option value="">Select a teacher</option>
            @foreach($row['choices'] as $choice)
                <option value="{{ $choice['id'] }}" @disabled(! $choice['eligible']) @selected((string) old('replacements.'.$row['key']) === (string) $choice['id'])>
                    {{ $choice['name'] }}{{ $choice['reason'] ? ' — schedule conflict' : '' }}
                </option>
            @endforeach
        </select>
        @error('replacements.'.$row['key'])
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
        @error($row['key'])
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>
</li>
