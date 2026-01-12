<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $req)
    {
        $creds = $req->only('email', 'password');
        if (!Auth::attempt($creds)) {
            return response()->json(['success' => false, 'error' => ['message' => 'Invalid credentials']], 401);
        }
        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    public function me(Request $req)
    {
        return response()->json(['success' => true, 'user' => $req->user()]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * (Optional) Browser redirect to Google – not used by Next.js but can keep it.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * (Optional) Browser callback from Google – not used by Next.js but can keep it.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Google authentication failed.'],
            ], 401);
        }

        return $this->handleGoogleUser($googleUser);
    }


    public function loginWithGoogleToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string', // this is ID TOKEN
        ]);

        // Verify token with Google
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->token,
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Failed to verify Google token.'],
            ], 401);
        }

        $payload = $response->json();
        // dd($payload);
        // Check audience matches your client ID
        if (($payload['aud'] ?? null) != config('services.google.client_id')) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Invalid token audience.'],
            ], 401);
        }

        $googleUser = (object) [
            'id'     => $payload['sub'],
            'name'   => $payload['name'] ?? ($payload['given_name'] ?? 'Google User'),
            'email'  => $payload['email'] ?? null,
            'avatar' => $payload['picture'] ?? null,
        ];

        // Reuse your existing logic
        return $this->handleGoogleUser(new class($googleUser) {
            public $user;
            public function __construct($user) { $this->user = $user; }
            public function getId()    { return $this->user->id; }
            public function getName()  { return $this->user->name; }
            public function getEmail() { return $this->user->email; }
            public function getAvatar(){ return $this->user->avatar; }
        });
    }

    public function loginWithFacebookToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string', // Facebook ACCESS TOKEN
        ]);

        // Call Facebook Graph API to validate token & get user info
        $response = Http::get('https://graph.facebook.com/me', [
            'fields'       => 'id,name,email,picture',
            'access_token' => $request->token,
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Failed to verify Facebook token.'],
            ], 401);
        }

        $data = $response->json();

        // Wrap as object with same interface used by handleSocialUser
        $facebookUser = new class($data)
        {
            public function __construct(public array $data) {}

            public function getId()
            {
                return $this->data['id'] ?? null;
            }

            public function getName()
            {
                return $this->data['name'] ?? 'Facebook User';
            }

            public function getEmail()
            {
                return $this->data['email'] ?? null;
            }

            public function getAvatar()
            {
                // Picture comes like ['picture' => ['data' => ['url' => '...']]]
                return $this->data['picture']['data']['url'] ?? null;
            }
        };

        return $this->handleSocialUser('facebook', $facebookUser);
    }



    /**
     * Common logic to find/create user and return Sanctum token.
     */
    protected function handleGoogleUser($googleUser)
    {
        return $this->handleSocialUser('google', $googleUser);
    }


    protected function handleSocialUser(string $provider, $socialUser)
    {
        // 1. Try find by provider + provider_id
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        // 2. If not found, try by email
        if (!$user && $socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())->first();
        }

        // 3. If still not found, create new
        if (!$user) {
            $user = User::create([
                'name'        => $socialUser->getName() ?? ucfirst($provider) . ' User',
                'email'       => $socialUser->getEmail(),
                'password'    => bcrypt(Str::random(16)),
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar'      => $socialUser->getAvatar(),
            ]);
        } else {
            $user->update([
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar'      => $socialUser->getAvatar(),
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $user,
        ]);
    }

}
