<x-mail::message>
# New Contact Form Submission

You have received a new message from your website's contact form.

<x-mail::panel>
**Name:** {{ $data['fname'] }} {{ $data['lname'] }}  
**Email:** {{ $data['email'] }}  
**Phone:** {{ $data['phone'] }}
</x-mail::panel>

## Message:

{{ $data['message'] }}

<x-mail::button :url="'mailto:' . $data['email']">
Reply to {{ $data['fname'] }}
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>