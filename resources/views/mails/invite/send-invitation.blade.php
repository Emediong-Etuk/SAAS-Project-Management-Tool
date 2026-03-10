<div>
    <!-- Knowing is not enough; we must apply. Being willing is not enough; we must do. - Leonardo da Vinci -->
    Use this link to join {{ config('app.url').'/api/accept-invitation?token='.$inviteCode }}
    Expires in {{ $expiryTime/60 }} minutes.

</div>
