
@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Teachers</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('teacher/list/page') }}">Teachers</a></li>
                        <li class="breadcrumb-item active">Edit Teachers</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('teacher/update') }}" method="POST">
                            @csrf
                            <input type="hidden" class="form-control" name="id" value="{{ $teacher->id }}">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="form-title"><span>Basic Details</span></h5>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>Name <span class="login-danger">*</span></label>
                                        <input type="text" class="form-control @error('full_name') is-invalid @enderror" name="full_name" placeholder="Enter Name" value="{{ $teacher->full_name }}">
                                        @error('full_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>Gender <span class="login-danger">*</span></label>
                                        <select class="form-control select  @error('gender') is-invalid @enderror" name="gender">
                                            <option selected disabled>Select Gender</option>
                                            <option value="Female" {{ $teacher->gender == 'Female' ? "selected" :"Female"}}>Female</option>
                                            <option value="Male" {{ $teacher->gender == 'Male' ? "selected" :""}}>Male</option>
                                            <option value="Others" {{ $teacher->gender == 'Others' ? "selected" :""}}>Others</option>
                                        </select>
                                        @error('gender')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms calendar-icon">
                                        <label>Date Of Birth <span class="login-danger">*</span></label>
                                        <input type="date" class="form-control js-dob @error('date_of_birth') is-invalid @enderror" name="date_of_birth" max="{{ date('Y-m-d') }}" min="1950-01-01" value="{{ old('date_of_birth', $teacher->date_of_birth ? \Illuminate\Support\Str::of($teacher->date_of_birth)->substr(0,10) : '') }}">
                                        @error('date_of_birth')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms calendar-icon">
                                        <label>Joining Date <span class="login-danger">*</span></label>
                                        <input type="text" class="form-control @error('joining_date') is-invalid @enderror" name="joining_date" value="{{ $teacher->join_date}}" readonly>
                                        @error('joining_date')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4 local-forms">
                                    <div class="form-group local-forms calendar-icon">
                                        <label>Qualification <span class="login-danger">*</span></label>
                                        <input type="text" class="form-control @error('qualification') is-invalid @enderror" name="qualification" placeholder="e.g. Bachelor of Education" value="{{ old('qualification', $teacher->qualification) }}">
                                        @error('qualification')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>Experience <span class="login-danger">*</span></label>
                                        <input type="text" class="form-control @error('experience') is-invalid @enderror" name="experience" placeholder="Enter Experience" value="{{ $teacher->experience }}">
                                        @error('experience')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <h5 class="form-title"><span>Address</span></h5>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>Country <span class="login-danger">*</span></label>
                                        <select class="form-control" name="country" id="teacher_country" required>
                                            <option value="Philippines" {{ old('country', $teacher->country ?: 'Philippines') === 'Philippines' ? 'selected' : '' }}>Philippines</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>State / Province <span class="login-danger">*</span></label>
                                        <select class="form-control" name="state" id="teacher_state" required>
                                            @php
                                                $provinces = ['Laguna','Metro Manila','Cavite','Batangas','Rizal','Quezon','Bulacan','Pampanga','Other'];
                                                $curState = old('state', $teacher->state);
                                            @endphp
                                            <option value="">Select Province</option>
                                            @foreach($provinces as $p)
                                                <option value="{{ $p }}" {{ $curState === $p ? 'selected' : '' }}>{{ $p }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>City <span class="login-danger">*</span></label>
                                        <select class="form-control" name="city" id="teacher_city" required>
                                            @php
                                                $cities = [
                                                    'Laguna' => ['City of Santa Rosa','Biñan','Cabuyao','Calamba','San Pedro','Los Baños','Other'],
                                                    'Metro Manila' => ['Manila','Quezon City','Makati','Pasig','Taguig','Other'],
                                                    'Cavite' => ['Bacoor','Imus','Dasmariñas','General Trias','Other'],
                                                    'Other' => ['Other'],
                                                ];
                                                $curCity = old('city', $teacher->city);
                                                $cityOpts = $cities[$curState] ?? ['Other'];
                                            @endphp
                                            <option value="">Select City</option>
                                            @foreach($cityOpts as $c)
                                                <option value="{{ $c }}" {{ $curCity === $c ? 'selected' : '' }}>{{ $c }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-8">
                                    <div class="form-group local-forms">
                                        <label>Street / Barangay Address <span class="login-danger">*</span></label>
                                        <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" placeholder="Street, barangay, subdivision" value="{{ old('address', $teacher->address) }}">
                                        @error('address')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>Zip Code</label>
                                        <input type="text" class="form-control" name="zip_code" value="{{ old('zip_code', $teacher->zip_code) }}" pattern="[0-9A-Za-z\-\s]+">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="form-group local-forms">
                                        <label>Phone <span class="login-danger">*</span></label>
                                        <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" inputmode="numeric" pattern="[0-9+\-\s()]+" placeholder="09xxxxxxxxx" value="{{ old('phone_number', $teacher->phone_number) }}">
                                        @error('phone_number')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                                @push('script')
                                <script>
                                (function(){
                                    const map = {
                                        'Laguna': ['City of Santa Rosa','Biñan','Cabuyao','Calamba','San Pedro','Los Baños','Other'],
                                        'Metro Manila': ['Manila','Quezon City','Makati','Pasig','Taguig','Other'],
                                        'Cavite': ['Bacoor','Imus','Dasmariñas','General Trias','Other'],
                                        'Batangas': ['Batangas City','Lipa','Tanauan','Other'],
                                        'Rizal': ['Antipolo','Cainta','Taytay','Other'],
                                        'Quezon': ['Lucena','Other'],
                                        'Bulacan': ['Malolos','Meycauayan','Other'],
                                        'Pampanga': ['Angeles','San Fernando','Other'],
                                        'Other': ['Other']
                                    };
                                    const state = document.getElementById('teacher_state');
                                    const city = document.getElementById('teacher_city');
                                    if (!state || !city) return;
                                    state.addEventListener('change', function(){
                                        const list = map[this.value] || ['Other'];
                                        city.innerHTML = '<option value="">Select City</option>' + list.map(c => `<option value="${c}">${c}</option>`).join('');
                                    });
                                })();
                                </script>
                                @endpush
                                <div class="col-12">
                                    <div class="student-submit">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
