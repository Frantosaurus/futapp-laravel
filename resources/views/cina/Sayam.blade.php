@extends('sablony.sablona')
@section('kontent')
<section>
    <div class="telo">
        <div class="nadpis col-auto">
            <h1>Sayam</h1>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="google-map">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d20382.0676709283!2d13.53760615!3d50.3150995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x470aed2f646021bd%3A0x744d4e4064b44539!2sSAYAM!5e0!3m2!1scs!2scz!4v1704735655523!5m2!1scs!2scz"
                        width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
            <div class="col-sm">
                <div class="podnadpis">
                    <label><strong>Adresa:</strong> Stavbařská 2, 301 00 Plzeň 3</label>
                    <br>
                    <label><strong>Tel. číslo:</strong> 778 889 888 </label>
                </div>
                <div class="oteviraci_doba">
                    <h6> <strong>Otevírací doba:</strong></h6>
                    <div><strong><span>Pondělí:</span></strong> 9-21</div>
                    <div><strong><span>Úterý:</span></strong> 9-21</div>
                    <div><strong><span>Středa:</span></strong> 9-21</div>
                    <div><strong><span>Čtvrtek:</span></strong> 9-21</div>
                    <div><strong><span>Pátek:</span></strong> 9-21</div>
                    <div><strong><span>Sobota:</span></strong> 9-21</div>
                    <div><strong><span>Neděle:</span></strong> 9-21</div>
                </div>
            </div>
            <form action="{{ route('celamezipametToCacheAndSaveToDatabase') }}" method="POST">
                @csrf
                <input type="hidden" name="restaurant_name" value="Sayam">
                <input type="hidden" name="restaurant_type" value="Asijská kuchyně">
                <div class="d-flex justify-content-center align-items-center">
                    <button type="button" class="btn btn-outline-dark" onclick="window.location.href='/cesko'"
                        style="margin-right: 30px;">Vrátit se</button>
                    <button type="submit" class="btn btn-outline-dark">Pokračovat</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection