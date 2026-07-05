<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label"><i class="bi bi-person"></i> Nom <span class="text-danger">*</span></label>
        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $patient->nom ?? '') }}" placeholder="Nom de famille" required>
        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label"><i class="bi bi-person"></i> Prénom <span class="text-danger">*</span></label>
        <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror" value="{{ old('prenom', $patient->prenom ?? '') }}" placeholder="Prénom" required>
        @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $patient->email ?? '') }}" placeholder="patient@email.com">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label"><i class="bi bi-telephone"></i> Téléphone</label>
        <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror" value="{{ old('telephone', $patient->telephone ?? '') }}" placeholder="+216 XX XXX XXX">
        @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="mb-3">
    <label class="form-label"><i class="bi bi-geo-alt"></i> Adresse</label>
    <textarea name="adresse" class="form-control @error('adresse') is-invalid @enderror" rows="2" placeholder="Adresse complète...">{{ old('adresse', $patient->adresse ?? '') }}</textarea>
    @error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label"><i class="bi bi-calendar"></i> Date de naissance</label>
        <input type="date" name="date_naissance" class="form-control @error('date_naissance') is-invalid @enderror" value="{{ old('date_naissance', isset($patient) && $patient->date_naissance ? $patient->date_naissance->format('Y-m-d') : '') }}">
        @error('date_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label"><i class="bi bi-gender-ambiguous"></i> Sexe</label>
        <select name="sexe" class="form-select @error('sexe') is-invalid @enderror">
            <option value="">Non précisé</option>
            <option value="M" {{ old('sexe', $patient->sexe ?? '') === 'M' ? 'selected' : '' }}>Masculin</option>
            <option value="F" {{ old('sexe', $patient->sexe ?? '') === 'F' ? 'selected' : '' }}>Féminin</option>
        </select>
        @error('sexe')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>