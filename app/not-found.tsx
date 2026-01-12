import Link from "next/link";

export default function NotFound() {
  return (
    <main className="flex min-h-screen items-center justify-center bg-primary">
      <div className="text-center text-white px-6">
        <h1 className="text-4xl font-bold mb-4">404 - Page Not Found</h1>
        <p className="text-lg text-gray-300">
          Oops! The page you are looking for does not exist.
        </p>
        <p className="text-sm text-gray-400 mt-6">
          Go back to{" "}
          <Link href="/" className="underline text-white">
            Home
          </Link>
          .
        </p>
      </div>
    </main>
  );

  // return (
  //   <main className="flex min-h-screen items-center justify-center bg-primary">
  //     <div className="text-center text-white px-6">
  //       <h1 className="text-4xl font-bold mb-4">🚧 Page in Progress</h1>
  //       <p className="text-lg text-gray-300">
  //         We’re currently working hard to build something amazing here.
  //       </p>
  //       <p className="text-sm text-gray-400 mt-6">Please check back soon.</p>
  //     </div>
  //   </main>
  // );
}
