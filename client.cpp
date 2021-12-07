class Client
{
	public:
	int _id;
	Client()
	{
		static int id = 0;
		_id = id++;
	}
};

#include <iostream>

int main()
{
	Client a, b, c;
	std::cout << a._id << " ; " << b._id << " ; " << c._id << std::endl;
}