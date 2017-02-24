<?php
namespace Prettus\RequestLogger\Middlewares;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Prettus\RequestLogger\Jobs\LogTask;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Closure;
use Route;

class ResponseLoggerMiddleware
{
    use DispatchesJobs;

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if(!$this->excluded($request)) {                    
            $task = new LogTask($request, $response);

            if($queueName = config('request-logger.queue')) {
                $this->dispatch(is_string($queueName) ? $task->onQueue($queueName) : $task);
            } else {
                $task->handle();
            }
        }

        return $response;
    }

    protected function excluded(Request $request) {
        $exclude = config('request-logger.exclude');
		
		if (null === $exclude || empty($exclude)) {
			return false;
		}

        foreach($exclude as $path) {
            if($request->is($path)) return true;
        }

        return false;
    }
}
